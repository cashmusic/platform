<?php
// add unique page settings:
$page_title = 'Commerce: Items';
$page_tips = "Add the items you want to sell in commerce-related elements.";

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getitemsforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'all_items'
);
?>