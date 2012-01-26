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
		'cash_action' => 'getlist',
		'list_id' => $request_parameters[0]
	),
	'getlist'
);

if ($current_response['status_uid'] == 'people_getlist_200') {
	$page_title = 'People: Edit "' . $current_response['payload']['name'] . '"';
	// parsing posted data:
	if (isset($_POST['dolistedit'])) {
		// do the actual list add stuffs...
		$effective_user = AdminHelper::getPersistentData('cash_effective_user');
		var_dump($_POST);
		$list_edit_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'editlist',
				'list_id' => $request_parameters[0],
				'name' => $_POST['list_name'],
				'description' => $_POST['list_description'],
				'connection_id' => $_POST['connection_id']
			)
		);
		$cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getlist',
				'list_id' => $request_parameters[0]
			),
			'getlist'
		);
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/');
}

?>