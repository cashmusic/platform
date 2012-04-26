<?php
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