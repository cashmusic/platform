<?php
 // Identify the workflow state:
if (AdminHelper::elementFormSubmitted($_POST)) {
	if (isset($_POST['skip_login'])) {
		$skip_login = 1;
	} else {
		$skip_login = 0;
	}
	AdminHelper::handleElementFormPOST(
		$_POST,
		$cash_admin,
		array(
			'alternate_password' => $_POST['alternate_password'],
			'message_success' => $_POST['message_success'],
			'email_list_id' => $_POST['email_list_id'],
			'skip_login' => $skip_login,
			'asset_id' => $_POST['asset_id']
		)
	);
}

// Page data needed for a blank 'add' form:
$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions('items',0,false,true);
$cash_admin->page_data['options_assets'] = AdminHelper::echoFormOptions('assets',0,false,true);
$current_element = $cash_admin->getCurrentElement();
if ($current_element) {
	// Current element found, so fill in the 'edit' form, basics first:
	AdminHelper::setBasicElementFormData($cash_admin);
	// Now any element-specific options:
	$cash_admin->page_data['options_alternate_password'] = $current_element['options']['alternate_password'];
	$cash_admin->page_data['options_message_success'] = $current_element['options']['message_success'];
	$cash_admin->page_data['options_skip_login'] = $current_element['options']['skip_login'];
	$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions('people_lists',$current_element['options']['email_list_id'],false,true);
	$cash_admin->page_data['options_assets'] = AdminHelper::echoFormOptions('assets',$current_element['options']['asset_id'],false,true);
}
?>