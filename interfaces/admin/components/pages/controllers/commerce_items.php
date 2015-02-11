<?php
$items_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getitemsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

$releases_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getassetsforuser',
		'type' => 'release',
		'parent_id' => 0,
		'user_id' => $cash_admin->effective_user_id
	)
);

$cash_admin->page_data['assets_options'] = AdminHelper::echoFormOptions('assets',false,$cash_admin->getAllFavoriteAssets(),true);

if (is_array($items_response['payload'])) {
	// IF there is an attached asset and IF it's a release then say so
	foreach ($items_response['payload'] as &$item) {
		if (is_array($releases_response['payload'])) {
			foreach ($releases_response['payload'] as $release) {
				if ($item['fulfillment_asset'] == $release['id']) {
					$item['release_asset'] = true;
					break;
				}
			}
		}
	}

	$cash_admin->page_data['items_all'] = new ArrayIterator(array_reverse($items_response['payload']));
}

$cash_admin->setPageContentTemplate('commerce_items');
?>