<?php
$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getitemsforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'all_items'
);
?>