<?php
if (isset($_POST['doitemadd'])) {
	// do the actual list add stuffs...
	$effective_user = $cash_admin->effective_user_id;
	$flexible_price = 0;
	if (isset($_POST['item_flexible_price'])) {
		$flexible_price = 1;
	}
	$add_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce', 
			'cash_action' => 'additem',
			'user_id' => $effective_user,
			'name' => $_POST['item_name'],
			'description' => $_POST['item_description'],
			'price' => $_POST['item_price'],
			'flexible_price' => $flexible_price,
			'digital_fulfillment' => 1,
			'fulfillment_asset' => $_POST['item_fulfillment_asset']
		)
	);
	if ($add_response['payload']) {
		AdminHelper::formSuccess('Success. Item added.','/commerce/items/edit/' . $add_response['payload']);
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/commerce/items/');
	}
	$item_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce', 
			'cash_action' => 'getitem',
			'id' => $add_response['payload']
		)
	);
} else {
	// parsing posted data:
	if (isset($_POST['doitemedit'])) {
		// do the actual list add stuffs...
		$item_id = $request_parameters[0];
		if (!isset($_POST['item_fulfillment_asset'])) {
			$_POST['item_fulfillment_asset'] = 0;
		}
		$flexible_price = 0;
		if (isset($_POST['item_flexible_price'])) {
			$flexible_price = 1;
		}
		$edit_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'edititem',
				'name' => $_POST['item_name'],
				'description' => $_POST['item_description'],
				'price' => $_POST['item_price'],
				'flexible_price' => $flexible_price,
				'fulfillment_asset' => $_POST['item_fulfillment_asset'],
				'id' => $item_id
			)
		);
		if ($edit_response['status_uid'] == 'commerce_edititem_200') {
			AdminHelper::formSuccess('Success. Edited.');
		} else {
			AdminHelper::formFailure('Error. There was a problem editing.');
		}
	}
	$item_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce', 
			'cash_action' => 'getitem',
			'id' => $request_parameters[0]
		)
	);
}

if (is_array($item_response['payload'])) {
	$cash_admin->page_data = array_merge($cash_admin->page_data,$item_response['payload']);
	if (isset($_POST['doitemadd'])) {
		$cash_admin->page_data['page_message'] = 'Success. Event added.';
	}
	$cash_admin->page_data['asset_options'] = AdminHelper::echoFormOptions('assets',$item_response['payload']['fulfillment_asset'],$cash_admin->getAllFavoriteAssets(),true);
}

$cash_admin->page_data['form_state_action'] = 'doitemedit';
$cash_admin->setPageContentTemplate('commerce_items_details');
?>