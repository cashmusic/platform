<?php
// allow signups?
$signups = (defined('ALLOW_SIGNUPS')) ? ALLOW_SIGNUPS : true;
// filter signups?
$filter_signups = false;
if (defined('FILTER_SIGNUPS')) {
	if (FILTER_SIGNUPS) {
		$filter_signups = FILTER_SIGNUPS;
	}
}
// minimum password length
$cash_admin->page_data['minimum_password_length'] = (defined('MINIMUM_PASSWORD_LENGTH')) ? MINIMUM_PASSWORD_LENGTH : 10;

if (substr(trim($_REQUEST['p'],'/'),0,6) == 'signup' && $signups) {
	if (isset($_POST['dosignup'])) {
		if (!empty($_POST['address']) && isset($_POST['termsread'])) {
			if(filter_var($_POST['address'], FILTER_VALIDATE_EMAIL)) {
				$approved_address = true;
				if ($filter_signups) {
					if (strpos($_POST['address'],$filter_signups) === false) {
						$approved_address = false;
						AdminHelper::formFailure('This site restricts signups. Please enter a new email and try again.','/');
					}
				}
				if ($approved_address) {
					$username_success = false;
					$final_username = strtolower(preg_replace("/[^a-z0-9]+/i", '',$_POST['username']));
					if (!empty($final_username)) {
						$username_request = new CASHRequest(
							array(
								'cash_request_type' => 'people',
								'cash_action' => 'getuseridforusername',
								'username' => $final_username
							)
						);
						if ($username_request->response['payload']) {
							$final_username = strtolower(str_replace(array('@','.'),'',$_POST['address']));
						} else {
							$username_success = true;
						}
					} else {
						$final_username = strtolower(str_replace(array('@','.'),'',$_POST['address']));
					}

					$add_request = new CASHRequest(
						array(
							'cash_request_type' => 'system',
							'cash_action' => 'addlogin',
							'address' => $_POST['address'],
							'password' => $_POST['password'],
							'is_admin' => 0,
							'username' => $final_username,
							'data' => array('agreed_terms' => time())
						)
					);

					$reset_key = $cash_admin->requestAndStore(
						array(
							'cash_request_type' => 'system',
							'cash_action' => 'setresetflag',
							'address' => $_POST['address']
						)
					);
					$reset_key = $reset_key['payload'];

					if ($add_request->response['payload']) {
						CASHSystem::sendEmail(
							'Your CASH Music account is ready',
							false,
							$_POST['address'],
							'Your CASH Music account has been created. '
								. 'To get started you just need to activate it by visiting: '
								. "\n\n"
								. CASH_ADMIN_URL . '/verify?key=' . $reset_key . '&address=' . urlencode($_POST['address'])
								. "\n\n"
								. '',
							'Welcome to CASH Music'
						);
						AdminHelper::formSuccess('Thanks. Check your inbox for instructions.','/');
					}
				}
			} else {
				AdminHelper::formFailure('Please enter a valid email address.','/');
			}
		} else {
			AdminHelper::formFailure('Make sure you have agreed to the terms of service.','/');
		}
	}

	$cash_admin->page_data['ui_title'] = 'Sign up now';
	$cash_admin->setPageContentTemplate('signup');
} else if (substr(trim($_REQUEST['p'],'/'),0,13) == 'resetpassword') {
	if (isset($_POST['dopasswordreset'])) {
		if (filter_var($_POST['address'], FILTER_VALIDATE_EMAIL)) {
			$reset_key = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'system',
					'cash_action' => 'setresetflag',
					'address' => $_POST['address']
				)
			);
			$reset_key = $reset_key['payload'];
			if ($reset_key) {
				$reset_message = 'A password reset was requested for this email address. If you didn\'t request the '
							   . 'reset simply ignore this message and no change will be made. To reset your password '
							   . 'follow this link: '
							   . "\n\n"
							   . CASH_ADMIN_URL . '/setpassword?key=' . $reset_key . '&address=' . urlencode($_POST['address']) // <-- the underscore for urls ending with a / ...i dunno. probably fixable via htaccess
							   . "\n\n"
							   . 'Thank you.';
				CASHSystem::sendEmail(
					'A password reset has been requested',
					false,
					$_POST['address'],
					$reset_message,
					'Reset your password?'
				);
				AdminHelper::formSuccess('Thanks. Check your inbox for instructions.','/');
			} else {
				AdminHelper::formFailure('Please check the address and try again.','/');
			}
		} else {
			AdminHelper::formFailure('Please check the address and try again.','/');
		}
	}

	if (isset($_POST['key'])) {

	}

	$cash_admin->page_data['ui_title'] = 'Reset password';
	$cash_admin->setPageContentTemplate('resetpassword');
} else {
	// this for returning password reset people:
	if (substr(trim($_REQUEST['p'],'/'),0,11) == 'setpassword') {
		$valid_key = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'validateresetflag',
				'address' => $_GET['address'],
				'key' => $_GET['key']
			)
		);
		if ($valid_key) {
			$cash_admin->page_data['reset_key'] = $_GET['key'];
			$cash_admin->page_data['reset_email'] = $_GET['address'];
			$cash_admin->page_data['reset_action'] = CASH_ADMIN_URL . '/';
		}
	}

	if (substr(trim($_REQUEST['p'],'/'),0,6) == 'verify') {
		if (isset($_GET['key'])) {
			$valid_key = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'system',
					'cash_action' => 'validateresetflag',
					'address' => $_GET['address'],
					'key' => $_GET['key']
				)
			);
			if ($valid_key) {
				$id_response = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'people',
						'cash_action' => 'getuseridforaddress',
						'address' => $_GET['address']
					)
				);
				if ($id_response['payload']) {
					$change_response = $cash_admin->requestAndStore(
						array(
							'cash_request_type' => 'system',
							'cash_action' => 'setlogincredentials',
							'user_id' => $id_response['payload'],
							'is_admin' => 1
						)
					);
					if ($change_response['payload'] !== false) {
						// mark user as logged in
						$admin_primary_cash_request->startSession();
						$admin_primary_cash_request->sessionSet('cash_actual_user',$id_response['payload']);
						$admin_primary_cash_request->sessionSet('cash_effective_user',$id_response['payload']);
						$admin_primary_cash_request->sessionSet('cash_effective_user_email',$address);

						// handle initial login chores
						$cash_admin->runAtLogin();
						AdminHelper::formSuccess('Welcome!','/');
					} else {
						AdminHelper::formFailure('Please try again.','/');
					}
				} else {
					AdminHelper::formFailure('Please try again.','/');
				}
			}
		}
	}

	// and this for the actual password reset after return folks submit the reset form:
	if (isset($_POST['finalizepasswordreset'])) {
		$valid_key = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'validateresetflag',
				'address' => $_POST['address'],
				'key' => $_POST['key']
			)
		);
		if ($valid_key) {
			$id_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'people',
					'cash_action' => 'getuseridforaddress',
					'address' => $_POST['address']
				)
			);
			if ($id_response['payload']) {
				$change_response = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'system',
						'cash_action' => 'setlogincredentials',
						'user_id' => $id_response['payload'],
						'address' => $_POST['address'],
						'password' => $_POST['new_password'],
						'is_admin' => 1
					)
				);
				if ($change_response['payload'] !== false) {
					AdminHelper::formSuccess('Successfully changed the password. Go ahead and log in.','/');
				} else {
					AdminHelper::formFailure('There was an error setting your password. Please try again.','/');
				}
			} else {
				AdminHelper::formFailure('There was an error setting the password. Please try again.','/');
			}
		}
	}

	$cash_admin->setPageContentTemplate('login');
}
?>
