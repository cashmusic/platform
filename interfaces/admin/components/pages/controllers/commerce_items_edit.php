<?php
if (isset($_POST['doitemadd'])) {
	// do the actual list add stuffs...
	$flexible_price = 0;
	if (isset($_POST['item_flexible_price'])) {
		$flexible_price = 1;
	}
	$physical = 0;
	if (isset($_POST['item_physical'])) {
		$physical = 1;
	}
	$add_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce',
			'cash_action' => 'additem',
			'user_id' => $cash_admin->effective_user_id,
			'name' => $_POST['item_name'],
			'description' => $_POST['item_description'],
			'price' => $_POST['item_price'],
			'available_units' => $_POST['item_quantity'],
			'flexible_price' => $flexible_price,
			'digital_fulfillment' => 1,
			'fulfillment_asset' => $_POST['item_fulfillment_asset'],
			'physical_fulfillment' => $physical
		)
	);
	if ($add_response['payload']) {
		AdminHelper::formSuccess('Success. Item added.','/commerce/items/' . $add_response['payload']);
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
		$physical = 0;
		if (isset($_POST['item_physical'])) {
			$physical = 1;
		}
		$edit_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'edititem',
				'name' => $_POST['item_name'],
				'description' => $_POST['item_description'],
				'price' => $_POST['item_price'],
				'available_units' => $_POST['item_quantity'],
				'flexible_price' => $flexible_price,
				'fulfillment_asset' => $_POST['item_fulfillment_asset'],
				'physical_fulfillment' => $physical,
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

/* ITEM VARIANTS */
if (isset($_POST['configure_variants'])) {
	// TODO:
	// DO THE VARIANTS
	// DO THEM!
	//
	// EXAMPLE print_r of form output:
	//
	// Array ( [configure_variants] => makeitso [item_id] => 13 [primary_variant_name] => color [secondary_variant_name] => butts [optionname] => [optionname-clone-primary-options-0] => hahaha [optionname-clone-primary-options-1] => haha [optionname-clone-primary-options-2] => ha [optionname-clone-secondary-options-0] => no [data_only] => 1 ) 
	$cash_admin->page_data['varrrrrrrrriants'] = print_r($_POST,true);
}

if (is_array($item_response['payload'])) {
	$cash_admin->page_data = array_merge($cash_admin->page_data,$item_response['payload']);
	if (isset($_POST['doitemadd'])) {
		$cash_admin->page_data['page_message'] = 'Success. Event added.';
	}
	$cash_admin->page_data['asset_options'] = AdminHelper::echoFormOptions('assets',$item_response['payload']['fulfillment_asset'],$cash_admin->getAllFavoriteAssets(),true);
}

$cash_admin->page_data['form_state_action'] = 'doitemedit';
$cash_admin->setPageContentTemplate('commerce_items_edit');
?>
