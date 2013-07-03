<?php
$elements_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getelementsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

$elements_data = AdminHelper::getElementsData();

if (is_array($elements_response['payload'])) {
	foreach ($elements_response['payload'] as &$element) {
		if (array_key_exists($element['type'],$elements_data)) {
			$element['type_name'] = $elements_data[$element['type']]['name'];
		}
	}
	$cash_admin->page_data['elements_for_user'] = new ArrayIterator($elements_response['payload']);
} 

$cash_admin->setPageContentTemplate('elements');
?>