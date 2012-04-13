<?php
// add unique page settings:
$page_title = 'Commerce: Item Details';
$page_tips = "";

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
		$cash_admin->requestAndStore(
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
?>