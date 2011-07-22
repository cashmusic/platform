<?php
// add unique page settings:
$page_title = 'People: View List';
$page_tips = '';

if ($request_parameters) {
	$request_list_id = $request_parameters[0];
	
	$page_request = new CASHRequest(
		array(
			'cash_request_type' => 'userlist', 
			'cash_action' => 'viewlist',
			'list_id' => $request_list_id
		)
	);
}
?>