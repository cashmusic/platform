<?php
// parsing posted data:
if (isset($_POST['dolistadd'])) {
	// do the actual list add stuffs...
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	$add_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'addlist',
			'name' => $_POST['list_name'],
			'description' => $_POST['list_description'],
			'connection_id' => $_POST['connection_id'],
			'user_id' => $effective_user,
		),
		'listadd'
	);

	if ($add_response['payload']) {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/edit/' . $add_response['payload']);
	} else {
		$cash_admin->page_data['error_message'] = 'Error. Something just didn\'t work right.';
	}
}

$cash_admin->page_data['connection_options'] = AdminHelper::echoConnectionsOptions('lists',0,true);
$cash_admin->page_data['form_state_action'] = 'dolistadd';
$cash_admin->page_data['list_button_text'] = 'Add the list';

$cash_admin->setPageContentTemplate('people_lists_details');
?>