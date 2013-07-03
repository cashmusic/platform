<?php
$list_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlistsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

// lists
if (is_array($list_response['payload'])) {
	$cash_admin->page_data['lists_all'] = new ArrayIterator($list_response['payload']);
}

$cash_admin->page_data['current_date'] = date('m/d/Y');
$cash_admin->setPageContentTemplate('people');
?>