<?php
// add unique page settings:
$page_title = 'Elements: View All Elements';
$page_tips = 'This page lists all your defined elements. Click any of them to see embed details, make edits, or delete them.';

$page_request = new CASHRequest(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getsupportedtypes'
	)
);

include_once(ADMIN_BASE_PATH.'/includes/helpers.php');
$elements_data = getElementsData();
?>