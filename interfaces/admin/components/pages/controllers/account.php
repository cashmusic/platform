<?php
if (isset($_POST['doaccountchange'])) {
	$valid_user_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'validatelogin',
			'address' => $_POST['email_address'],
			'password' => $_POST['password'],
			'require_admin' => true,
			'keep_session' => true
		)
	);

	if (!$valid_user_response['payload']) {
		AdminHelper::formFailure('Error. There was a problem with your password. Please try again.');
	} else {
		$changes = array(
			'cash_request_type' => 'system', 
			'cash_action' => 'setlogincredentials',
			'user_id' => $cash_admin->effective_user_id
		);
		if (isset($_POST['new_email_address'])) { 
			if ($_POST['new_email_address']) {
				$changes['address'] = $_POST['new_email_address'];
			}
		}
		if (isset($_POST['new_username'])) { 
			if ($_POST['new_username']) {
				// strip all non-alpha/numeric and push it all to lowercase for the sake of uniqueness
				$changes['username'] = strtolower(preg_replace("/[^a-z0-9-]+/i", '',$_POST['new_username']));
			}
		}
		if (isset($_POST['new_displayname'])) { 
			if ($_POST['new_displayname']) {
				$changes['display_name'] = $_POST['new_displayname'];
			}
		}
		if (isset($_POST['new_url'])) { 
			if ($_POST['new_url']) {
				$changes['url'] = $_POST['new_url'];
			}
		}
		if (isset($_POST['new_password'])) { 
			if ($_POST['new_password']) {
				if (!defined('MINIMUM_PASSWORD_LENGTH')) {
					define('MINIMUM_PASSWORD_LENGTH',10);
				}
				if (strlen($_POST['new_password']) < MINIMUM_PASSWORD_LENGTH) {
					AdminHelper::formFailure('Error. Your password should be at least ' . MINIMUM_PASSWORD_LENGTH . ' characters long. Please try again.');
				}
				$changes['password'] = $_POST['new_password'];
			}
		}
		$change_response = $cash_admin->requestAndStore($changes);
		if ($change_response['payload'] !== false) {
			if (isset($changes['address'])) {
				$admin_primary_cash_request->sessionSet('cash_effective_user_email',$changes['address']);
			}
			AdminHelper::formSuccess('Success. All changed.');
		} else {
			AdminHelper::formFailure('Error. We had a problem resetting your login. Please try again. Email addresses and usernames have to be unique.');
		}
	}
}

$effective_user = $cash_admin->effective_user_id;
$user_request = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getuser',
		'user_id' => $effective_user
	)
);

if (is_array($user_request['payload'])) {
	$cash_admin->page_data['email_address'] = $user_request['payload']['email_address'];
	$cash_admin->page_data['username'] = $user_request['payload']['username'];
	$cash_admin->page_data['display_name'] = $user_request['payload']['display_name'];
	$cash_admin->page_data['url'] = $user_request['payload']['url'];
	$cash_admin->page_data['api_key'] = $user_request['payload']['api_key'];
	$cash_admin->page_data['api_url'] = CASH_API_URL;
	if (isset($_REQUEST['reveal'])) {
		$cash_admin->page_data['api_secret'] = $user_request['payload']['api_secret'];
	}
}

// get username and any user data
$user_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getuser',
		'user_id' => $cash_admin->effective_user_id
	)
);


if (is_array($user_response['payload'])) {
	$current_username = $user_response['payload']['username'];
	$current_userdata = $user_response['payload']['data'];
}


//$data = $current_userdata; // now it's an array

// no form submit so let's check DB
$cash_admin->page_data['language'] = AdminHelper::getOrSetLanguage();
$cash_admin->page_data['language_as_options'] = AdminHelper::echoLanguageOptions(
	$cash_admin->page_data['language']
);

if (!empty($current_userdata['payload']) && $current_userdata['payload'] !== false) {
	if (isset($_POST['dolanguagechange'])) {
		if (isset($cash_admin->page_data['language'])) {
			AdminHelper::formSuccess('Success. Language changed.');
		} else {
			AdminHelper::formFailure('Error. We had a problem resetting your language. Please try again.');
		}
	}
}

// get page url
if (SUBDOMAIN_USERNAMES) {
	$cash_admin->page_data['user_page_uri'] = str_replace('https','http',rtrim(str_replace('admin', '', CASH_ADMIN_URL),'/'));
	$cash_admin->page_data['user_page_uri'] = str_replace('://','://' . $current_username . '.',$cash_admin->page_data['user_page_uri']);
} else {
	$cash_admin->page_data['user_page_uri'] = str_replace('https','http',rtrim(str_replace('admin', $current_username, CASH_ADMIN_URL),'/'));
}
$cash_admin->page_data['user_page_display_uri'] = str_replace('http://','',$cash_admin->page_data['user_page_uri']);

$cash_admin->setPageContentTemplate('account');
?>
