<?php
// add unique page settings:
$page_title = 'People: Main';
$page_tips = '';
$page_memu = array(
	'People' => array(
		'people/contacts/' => array('Contacts','user'),
		'people/lists/' => array('Lists','list'),
		'people/social/' => array('Social','chat')
	)
);

$current_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlistsforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'getlistsforuser'
);
?>