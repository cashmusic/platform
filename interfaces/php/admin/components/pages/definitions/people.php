<?php
// add unique page settings:
$page_title = 'People: Main';
$page_tips = '';
$page_memu = array(
	'Actions' => array(
		'people/mailinglists/' => 'Mailing Lists',
			'people/mailinglists/add/' => 'Add Mailing List',
			'people/mailinglists/view/' => 'View Mailing List',
			'people/mailinglists/export/' => 'Export Mailing List',
		'people/social/' => 'Social'
	)
);
$page_data = array();
$page_section_request = new CASHRequest(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlistsforuser',
		'user_id' => getPersistentData('cash_effective_user')
	)
);
$page_data['lists'] = $page_section_request->response['payload'];
?>