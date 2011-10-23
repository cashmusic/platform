<?php
// add unique page settings:
$page_title = 'People: Edit List';
$page_tips = '';

if (!$request_parameters) {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/');
}

$current_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlistinfo',
		'list_id' => $request_parameters[0]
	),
	'getlistinfo'
);

if ($current_response['status_uid'] == 'people_getlistinfo_200') {
	$page_title = 'People: Edit "' . $current_response['payload']['name'] . '"';
	// parsing posted data:
	if (isset($_POST['dolistedit'])) {
		// do the actual list add stuffs...
		$effective_user = AdminHelper::getPersistentData('cash_effective_user');
		$list_edit_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'editlist',
				'list_id' => $request_parameters[0],
				'list_name' => $_POST['list_name'],
				'list_description' => $_POST['list_description'],
				'settings_id' => $_POST['settings_id']
			)
		);
		$cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getlistinfo',
				'list_id' => $request_parameters[0]
			),
			'getlistinfo'
		);
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/');
}

?>