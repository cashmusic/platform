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
 * This file is generously sponsored by josh ayala 
 *
 **/

 // Identify the workflow state:
if (AdminHelper::elementFormSubmitted($_POST)) {
	AdminHelper::handleElementFormPOST(
		$_POST,
		$cash_admin,
		array(
			'message_success' => $_POST['message_success']
		)
	);
}

$current_element = $cash_admin->getCurrentElement();
if ($current_element) {
	// Current element found, so fill in the 'edit' form, basics first:
	AdminHelper::setBasicElementFormData($cash_admin);
	// Now any element-specific options:
	$cash_admin->page_data['options_message_success'] = $current_element['options']['message_success'];
}
?>