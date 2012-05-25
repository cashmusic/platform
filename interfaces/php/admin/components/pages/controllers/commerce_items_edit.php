<?php
if (isset($_POST['doitemadd'])) {
	// do the actual list add stuffs...
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	$add_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce', 
			'cash_action' => 'additem',
			'user_id' => $effective_user,
			'name' => $_POST['item_name'],
			'description' => $_POST['item_description'],
			'price' => $_POST['item_price'],
			'digital_fulfillment' => 1,
			'fulfillment_asset' => $_POST['item_fulfillment_asset']
		),
		'eventaddattempt'
	);
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce', 
			'cash_action' => 'getitem',
			'id' => $add_response['payload']
		),
		'getitem'
	);
} else {
	// parsing posted data:
	if (isset($_POST['doitemedit'])) {
		// do the actual list add stuffs...
		$item_id = $request_parameters[0];
		$event_edit_request = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'edititem',
				'name' => $_POST['item_name'],
				'description' => $_POST['item_description'],
				'price' => $_POST['item_price'],
				'fulfillment_asset' => $_POST['item_fulfillment_asset'],
				'id' => $item_id
			),
			'itemeditattempt'
		);
		if ($event_edit_request['status_uid'] == 'commerce_edititem_200') {
			$cash_admin->page_data['page_message'] = 'Success. Edited.';
		} else {
			$cash_admin->page_data['error_message'] = 'Error. There was a problem editing the item.';
		}
	}
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce', 
			'cash_action' => 'getitem',
			'id' => $request_parameters[0]
		),
		'getitem'
	);
}

$item_response = $cash_admin->getStoredResponse('getitem', true);
if (is_array($item_response)) {
	$cash_admin->page_data = array_merge($cash_admin->page_data,$item_response);
	if (isset($_POST['doitemadd'])) {
		$cash_admin->page_data['page_message'] = 'Success. Event added.';
	}
	$cash_admin->page_data['asset_options'] = AdminHelper::echoFormOptions('assets',$item_response['fulfillment_asset'],$cash_admin->getAllFavoriteAssets(),true);
}

$cash_admin->setPageContentTemplate('commerce_items_edit');
?>