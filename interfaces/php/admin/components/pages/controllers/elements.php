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

// banner stuff
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	$cash_admin->page_data['banner_title_content'] = '<h2>Elements</h2>manage your <b>contacts</b><br />create and maintain <b>lists</b><br />monitor <b>social</b> media';
	$cash_admin->page_data['banner_main_content'] = 'Combine everything else and build functionality, check analytics for existing elements, and get embed codes to use your elements on your site.';
}

$cash_admin->setPageContentTemplate('elements');
?>