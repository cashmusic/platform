<?php
// allow signups?
$signups = (defined('ALLOW_SIGNUPS')) ? ALLOW_SIGNUPS : true;
if (trim($_REQUEST['p'],'/') == 'signup' && $signups) {
	$cash_admin->page_data['ui_title'] = 'Sign up now';
	$cash_admin->setPageContentTemplate('signup');
} else if (trim($_REQUEST['p'],'/') == 'resetpassword') {
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
							   . CASHSystem::getCurrentURL()
							   . '?dopasswordreset=' . $reset_key . '&address=' . urlencode($_POST['address']) // <-- the underscore for urls ending with a / ...i dunno. probably fixable via htaccess
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

	$cash_admin->page_data['ui_title'] = 'Reset password';
	$cash_admin->setPageContentTemplate('resetpassword');
} else {
	// this for returning password reset people:
	$cash_admin->page_data['minimum_password_length'] = (defined('MINIMUM_PASSWORD_LENGTH')) ? MINIMUM_PASSWORD_LENGTH : 10;
	if (isset($_GET['dopasswordreset'])) {
		// minimum password length

		$valid_key = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validateresetflag',
				'address' => $_GET['address'],
				'key' => $_GET['dopasswordreset']
			)
		);
		if ($valid_key) {
			$cash_admin->page_data['reset_key'] = $_GET['dopasswordreset'];
			$cash_admin->page_data['reset_email'] = $_GET['address'];
			$cash_admin->page_data['reset_action'] = CASHSystem::getCurrentURL();
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
						'password' => $_POST['new_password']
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