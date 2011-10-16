<?php
// add unique page settings:
$page_title = 'Elements: Main';
$page_tips = 'This is where specific functionalities will be deployed, pages installed, and widgets set up. It is really the heart of the public-facing tools — the other tabs are more aimed at management, lightweight CRM, and fulfillment.';
$page_memu = array(
	'Elements' => array(
		'elements/view/' => 'Your Elements',
		'elements/add/' => 'Add Element'
	)
);

$element_page_request = new CASHRequest(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'mostactive',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	)
);
if ($element_page_request->response['status_code'] == 200) {
	$page_data['element_mostactive'] = $element_page_request->response['payload'];
} else {
	$page_data['element_mostactive'] = false;
}

$element_page_request = new CASHRequest(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'recentlyadded',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	)
);
if ($element_page_request->response['status_code'] == 200) {
	$page_data['element_recentlyadded'] = $element_page_request->response['payload'];
} else {
	$page_data['element_recentlyadded'] = false;
}
?>