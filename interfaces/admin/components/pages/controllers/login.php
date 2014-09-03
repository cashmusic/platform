<?php
// before we get all awesome and whatnot, detect for password reset stuff. should only happen 
// with a full page reload, not a data-only one as above
if (isset($_POST['dopasswordresetlink'])) {
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
						   . '_?dopasswordreset=' . $reset_key . '&address=' . urlencode($_POST['address']) // <-- the underscore for urls ending with a / ...i dunno. probably fixable via htaccess
						   . "\n\n"
						   . 'Thank you.';
			CASHSystem::sendEmail(
				'A password reset has been requested',
				false,
				$_POST['address'],
				$reset_message,
				'Reset your password?'
			);
			$cash_admin->page_data['reset_message'] = 'Thanks. Just sent an email with instructions. Check your SPAM filters if you do not see it soon.';
		} else {
			$cash_admin->page_data['reset_message'] = 'There was an error. Please check the address and try again.';
		}
	}
}

if (isset($_GET['showlegal'])) {
	$cash_admin->page_data['legal_markup'] = '';
	if (file_exists(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php')) {
		include_once(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php');
	}
	if (isset($cash_admin->page_data['showterms'])) {
		$cash_admin->page_data['legal_markup'] .= '<br /><br /><br /><h4>Terms of service</h4>';
		$cash_admin->page_data['legal_markup'] .= Markdown(file_get_contents(ADMIN_BASE_PATH . '/terms.md'));
	}
	if (isset($cash_admin->page_data['showprivacy'])) {
		$cash_admin->page_data['legal_markup'] .= '<br /><br /><br /><h4>Privacy policy</h4>';
		$cash_admin->page_data['legal_markup'] .= Markdown(file_get_contents(ADMIN_BASE_PATH . '/privacy.md'));
	}
}

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
				$cash_admin->page_data['reset_message'] = 'Successfully changed the password. Go ahead and log in.';
			} else {
				$cash_admin->page_data['reset_message'] = 'There was an error setting your password. Please try again.';
			}
		} else {
			$cash_admin->page_data['reset_message'] = 'There was an error setting the password. Please try again.';
		}
	}
}

$cash_admin->setPageContentTemplate('login');
?>