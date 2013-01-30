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
 * This file is generously sponsored by David Mason 
 *
 **/

 // Identify the workflow state:
if (AdminHelper::elementFormSubmitted($_POST)) {
	AdminHelper::handleElementFormPOST(
		$_POST,
		$cash_admin,
		array(
			'visible_event_types' => $_POST['visible_event_types'],
			'max_display_dates' => $_POST['max_display_dates']
		)
	);
}

$current_element = $cash_admin->getCurrentElement();
$cash_admin->page_data['options_upcoming_checked'] = true;
if ($current_element) {
	// Current element found, so fill in the 'edit' form, basics first:
	AdminHelper::setBasicElementFormData($cash_admin);
	// Now any element-specific options:
	$cash_admin->page_data['options_visible_event_types'] = $current_element['options']['visible_event_types'];
	$cash_admin->page_data['options_max_display_dates'] = $current_element['options']['max_display_dates'];
	$cash_admin->page_data['options_upcoming_checked'] = false;
	$cash_admin->page_data['options_archive_checked'] = false;
	$cash_admin->page_data['options_both_checked'] = false;
	if ($current_element['options']['visible_event_types'] == 'both') {
		$cash_admin->page_data['options_both_checked'] = true;
	} elseif ($current_element['options']['visible_event_types'] == 'archive') {
		$cash_admin->page_data['options_archive_checked'] = true;
	} else {
		$cash_admin->page_data['options_upcoming_checked'] = true;
	}
}
?>