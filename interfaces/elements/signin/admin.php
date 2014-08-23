<?php
 // Identify the workflow state:
if (AdminHelper::elementFormSubmitted($_POST)) {
	AdminHelper::handleElementFormPOST(
		$_POST,
		$cash_admin,
		array(
			'alternate_password' => $_POST['alternate_password'],
			'email_list_id' => $_POST['email_list_id'],
			'display_title' => $_POST['display_title'],
			'display_message' => $_POST['display_message']
		)
	);
}

// Page data needed for a blank 'add' form:
$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions('items',0,false,true);
$current_element = $cash_admin->getCurrentElement();
if ($current_element) {
	// Current element found, so fill in the 'edit' form, basics first:
	AdminHelper::setBasicElementFormData($cash_admin);
	// Now any element-specific options:
	$cash_admin->page_data['options_alternate_password'] = $current_element['options']['alternate_password'];
	$cash_admin->page_data['options_display_title'] = $current_element['options']['display_title'];
	$cash_admin->page_data['options_display_message'] = $current_element['options']['display_message'];
	$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions('people_lists',$current_element['options']['email_list_id'],false,true);
}
?>