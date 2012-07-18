<?php
 // Identify the workflow state:
if (AdminHelper::elementFormSubmitted($_POST)) {
	if (isset($_POST['do_not_verify'])) {
		$do_not_verify = 1;
	} else {
		$do_not_verify = 0;
	}
	AdminHelper::handleElementFormPOST(
		$_POST,
		$cash_admin,
		array(
			'message_invalid_email' => $_POST['message_invalid_email'],
			'message_privacy' => $_POST['message_privacy'],
			'message_success' => $_POST['message_success'],
			'email_list_id' => $_POST['email_list_id'],
			'asset_id' => $_POST['asset_id'],
			'comment_or_radio' => 0,
			'do_not_verify' => $do_not_verify
		)
	);
}

// Page data needed for a blank 'add' form:
$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions('people_lists',0,false,true);
$cash_admin->page_data['options_assets'] = AdminHelper::echoFormOptions('assets',0,false,true);
$current_element = $cash_admin->getCurrentElement();
if ($current_element) {
	// Current element found, so fill in the 'edit' form, basics first:
	AdminHelper::setBasicElementFormData($cash_admin);
	// Now any element-specific options:
	$cash_admin->page_data['options_message_invalid_email'] = $current_element['options']['message_invalid_email'];
	$cash_admin->page_data['options_message_success'] = $current_element['options']['message_success'];
	$cash_admin->page_data['options_message_privacy'] = $current_element['options']['message_privacy'];
	$cash_admin->page_data['options_do_not_verify'] = $current_element['options']['do_not_verify'];
	$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions('people_lists',$current_element['options']['email_list_id'],false,true);
	$cash_admin->page_data['options_assets'] = AdminHelper::echoFormOptions('assets',$current_element['options']['asset_id'],false,true);
}
?>