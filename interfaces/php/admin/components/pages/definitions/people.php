<?php
// add unique page settings:
$page_title = 'People: Main';
$page_tips = '';
$page_memu = array(
	'Actions' => array(
		'people/contacts/' => 'Contacts',
		'people/mailinglists/' => 'Lists',
			'people/mailinglists/add/' => 'Add Mailing List',
			'people/mailinglists/view/' => 'View Mailing List',
			'people/mailinglists/export/' => 'Export Mailing List',
		'people/social/' => 'Social'
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
if ($current_response['status_uid'] == 'people_getlistsforuser_200') {
	$cash_admin->storeData($current_response['payload'],'alllists');
}
?>