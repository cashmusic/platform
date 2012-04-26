<?php
$effective_user = AdminHelper::getPersistentData('cash_effective_user');

if ($request_parameters) {
	$order_details_request = new CASHRequest(
		array(
			'cash_request_type' => 'commerce', 
			'cash_action' => 'getorder',
			'id' => $request_parameters[0],
			'deep' => true
		)
	);
	$order_details = $order_details_request->response['payload'];
	if ($order_details['user_id'] == $effective_user) {
		$cash_admin->page_data['title'] = 'Commerce: Order #' . str_pad($order_details['id'],6,0,STR_PAD_LEFT);
		$order_details['order_date'] = date("M j, Y, g:i A", $order_details['modification_date']);

	} else {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/commerce/orders/');
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/commerce/orders/');
}
?>