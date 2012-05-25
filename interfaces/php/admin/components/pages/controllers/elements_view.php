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

$elements_data = AdminHelper::getElementsData();
foreach ($elements_response['payload'] as &$element) {
	if (array_key_exists($element['type'],$elements_data)) {
		$element['type_name'] = $elements_data[$element['type']]['name'];
	}
}

	
if ($elements_response) {
	$cash_admin->page_data['elements_for_user'] = new ArrayIterator($elements_response['payload']);
} else {
	$cash_admin->page_data['elements_for_user'] = false;
}

$cash_admin->setPageContentTemplate('elements_view');
?>