<?php
// add unique page settings:
$page_title = 'Elements: Add Elements';
$page_tips = 'Choose an element type and click the "Add this now" button.';

$page_request = new CASHRequest(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getsupportedtypes'
	)
);

$elements_data = AdminHelper::getElementsData();

if ($request_parameters) {
	$element_addtype = $request_parameters[0];
	if (isset($elements_data[$element_addtype])) {
		$page_title = 'Elements: Add ' . $elements_data[$element_addtype]->name . ' Element';
	}
	
	$supported_elements = $page_request->response['payload'];
	if (array_search($element_addtype, $supported_elements) !== false) {
		if (@file_exists(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_addtype . '/help.php')) {
			$page_tips = file_get_contents(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_addtype . '/help.php');
		}
	}
}
?>