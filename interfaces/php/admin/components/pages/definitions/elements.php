<?php
// add unique page settings:
$page_title = 'Elements: Main';
$page_tips = 'This is where specific functionalities will be deployed, pages installed, and widgets set up. It is really the heart of the public-facing tools — the other tabs are more aimed at management, lightweight CRM, and fulfillment.';
$page_memu = array(
	'Elements' => array(
		'elements/view/' => 'View Element',
		'elements/add/' => 'Add Element'
	)
);

$element_page_request = new CASHRequest(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'mostactive',
		'user_id' => getPersistentData('cash_effective_user')
	)
);
$page_data['element_mostactive'] = $element_page_request->response['payload'];

$element_page_request = new CASHRequest(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'recentlyadded',
		'user_id' => getPersistentData('cash_effective_user')
	)
);
$page_data['element_recentlyadded'] = $element_page_request->response['payload'];
?>