<?php
$elements_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getelementsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

if (is_array($elements_response['payload'])) {
	$cash_admin->page_data['elements_for_user'] = new ArrayIterator($elements_response['payload']);
} 

$cash_admin->setPageContentTemplate('elements');
?>