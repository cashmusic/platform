<?php
$items_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getitemsforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'all_items'
);

$cash_admin->page_data['assets_options'] = AdminHelper::echoFormOptions('assets',false,$cash_admin->getAllFavoriteAssets(),true);

if (is_array($items_response['payload'])) {
	$cash_admin->page_data['items_all'] = new ArrayIterator($items_response['payload']);
}

$cash_admin->setPageContentTemplate('commerce_items');
?>