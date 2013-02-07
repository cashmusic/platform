<?php
/**
 *
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 *
 * This file is generously sponsored by Jacqueline Mazza 
 *
 **/

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
			'email_list_id' => $_POST['email_list_id'],
			'secure_content' => $_POST['secure_content'],
		)
	);
}

// Page data needed for a blank 'add' form:
$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions('people_lists',0,false,true);
$current_element = $cash_admin->getCurrentElement();
if ($current_element) {
	// Current element found, so fill in the 'edit' form, basics first:
	AdminHelper::setBasicElementFormData($cash_admin);
	// Now any element-specific options:
	$cash_admin->page_data['options_alternate_password'] = $current_element['options']['alternate_password'];
	$cash_admin->page_data['options_secure_content'] = $current_element['options']['secure_content'];
	$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions('people_lists',$current_element['options']['email_list_id'],false,true);
}
?>