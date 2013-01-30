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
 * This file is generously sponsored by Ted Nugent
 * (No it's not. That's totaly a lie. But funny, right? Ted Nugent. Ha...)
 *
 **/

 // Identify the workflow state:
if (AdminHelper::elementFormSubmitted($_POST)) {
	AdminHelper::handleElementFormPOST(
		$_POST,
		$cash_admin,
		array(
			'message_error' => $_POST['message_error'],
			'message_success' => $_POST['message_success'],
			'item_id' => $_POST['item_id'],
			'connection_id' => $_POST['connection_id']
		)
	);
}

// Page data needed for a blank 'add' form:
$cash_admin->page_data['options_items_dropdown'] = AdminHelper::echoFormOptions('items',0,false,true);
$cash_admin->page_data['options_connections_dropdown'] = AdminHelper::echoConnectionsOptions('commerce',0,true);
$current_element = $cash_admin->getCurrentElement();
if ($current_element) {
	// Current element found, so fill in the 'edit' form, basics first:
	AdminHelper::setBasicElementFormData($cash_admin);
	// Now any element-specific options:
	$cash_admin->page_data['options_message_success'] = $current_element['options']['message_success'];
	$cash_admin->page_data['options_message_error'] = $current_element['options']['message_error'];
	$cash_admin->page_data['options_items_dropdown'] = AdminHelper::echoFormOptions('items',$current_element['options']['item_id'],false,true);
	$cash_admin->page_data['options_connections_dropdown'] = AdminHelper::echoConnectionsOptions('commerce',$current_element['options']['connection_id'],true);
}
?>