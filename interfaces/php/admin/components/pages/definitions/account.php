<?php
// add unique page settings:
$page_title = 'Your Account';
$page_tips = '';

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
	$post_message = false;
	if (!checkLogin()) {
		$post_message = 'There was a problem with your password. Please try again.';
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
			$post_message = 'All changed.';
		} else {
			$post_message = 'We had a problem resetting your login. Please try again.';
		}
	}
}

$effective_user = AdminHelper::getPersistentData('cash_effective_user');
$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getuser',
		'id' => $effective_user
	),
	'userdetails'
);
?>