<?php
// add unique page settings:
$page_title = 'People: View List';
$page_tips = '';

if ($request_parameters) {
	$request_list_id = $request_parameters[0];
	$current_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'getlist',
			'list_id' => $request_list_id
		),
		'listdetails'
	);
	$page_title = 'People: View "' . $current_response['payload']['name'] . '"';

	if ($current_response['payload']) {
		$cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'viewlist',
				'list_id' => $request_list_id
			),
			'listmembers'
		);
		
		$cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getanalytics',
				'analtyics_type' => 'listmembership',
				'list_id' => $request_list_id,
				'user_id' => AdminHelper::getPersistentData('cash_effective_user')
			),
			'listanalytics'
		);
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/');
}
?>