<?php
function checkLogin() {
	if (isset($_POST['email_address']) && isset($_POST['password'])) {
		$login_details = AdminHelper::doLogin($_POST['email_address'],$_POST['password']);
		if ($login_details !== false) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

if (isset($_POST['doemailchange']) || isset($_POST['dopasswordchange'])) {
	if (!checkLogin()) {
		$cash_admin->page_data['error_message'] = 'Error. There was a problem with your password. Please try again.';
	} else {
		$email_address = $_POST['email_address'];
		$password = $_POST['password'];
		if (isset($_POST['new_email_address'])) { $email_address = $_POST['new_email_address']; }
		if (isset($_POST['new_password'])) { $password = $_POST['new_password']; }
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'setlogincredentials',
				'user_id' => AdminHelper::getPersistentData('cash_effective_user'), 
				'address' => $email_address, 
				'password' => $password
			)
		);
		if ($login_request->response['payload'] !== false) {
			if (isset($_POST['doemailchange'])) {
				$admin_primary_cash_request->sessionSet('cash_effective_user_email',$email_address);
			}
			$cash_admin->page_data['page_message'] = 'Success. All changed.';
		} else {
			$cash_admin->page_data['error_message'] = 'Error. We had a problem resetting your login. Please try again.';
		}
	}
}

$effective_user = AdminHelper::getPersistentData('cash_effective_user');
$user_request = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getuser',
		'user_id' => $effective_user
	),
	'userdetails'
);

if (is_array($user_request['payload'])) {
	$cash_admin->page_data['email_address'] = $user_request['payload']['email_address'];
	$cash_admin->page_data['api_key'] = $user_request['payload']['api_key'];
	$cash_admin->page_data['api_url'] = CASH_API_URL;
	if (isset($_REQUEST['reveal'])) {
		$cash_admin->page_data['api_secret'] = $user_request['payload']['api_secret'];
	}
}

$cash_admin->setPageContentTemplate('account');
?>