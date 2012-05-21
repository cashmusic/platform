<?php
$effective_user = AdminHelper::getPersistentData('cash_effective_user');

$elements_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getelementsforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'getelementsforuser'
);
$cash_admin->page_data['elements_for_user'] = AdminHelper::simpleULFromResponse($elements_response);
$cash_admin->setPageContentTemplate('elements_view');
?>