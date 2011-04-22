<?php
// add unique page settings:
$page_title = 'Elements: Add Elements';
$page_tips = 'They give an email, you give a download.';

$page_request = new CASHRequest(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getsupportedtypes'
	)
);

include(ADMIN_BASE_PATH.'/includes/helpers.php');
$elements_data = getElementsData();

if ($request_parameters) {
	$element_addtype = $request_parameters[0];
	$page_title = 'Elements: Add ' . $elements_data[$element_addtype]->name . ' Element';
}
?>