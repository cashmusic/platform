<?php
// add unique page settings:
$page_title = 'Commerce: Main';
$page_tips = '';

$page_memu = array(
	'Assets' => array(
		'commerce/items/' => array('Items','box'),
		'commerce/orders/' => array('Orders','book')
	)
);

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getitemsforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'all_items'
);

?>