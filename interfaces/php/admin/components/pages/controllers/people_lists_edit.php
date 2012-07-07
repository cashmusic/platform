<?php
// parsing posted data:
if (isset($_POST['dolistedit'])) {
	// do the actual list add stuffs...
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	$edit_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'editlist',
			'list_id' => $request_parameters[0],
			'name' => $_POST['list_name'],
			'description' => $_POST['list_description'],
			'connection_id' => $_POST['connection_id']
		),
		'editresponse'
	);
	if ($edit_response['status_uid'] == 'people_editlist_200') {
		AdminHelper::formSuccess('Success. Edited.');
	} else {
		AdminHelper::formFailure('Error. There was a problem editing.');
	}
}

$current_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlist',
		'list_id' => $request_parameters[0]
	),
	'getlist'
);
$cash_admin->page_data['ui_title'] = 'People: Edit "' . $current_response['payload']['name'] . '"';

$current_list = $current_response['payload'];

if (is_array($current_list)) {
	$cash_admin->page_data = array_merge($cash_admin->page_data,$current_list);
}
if ($current_list['connection_id'] == 0) {
	$cash_admin->page_data['no_selected_connection'] = true;
}
$cash_admin->page_data['connection_options'] = AdminHelper::echoConnectionsOptions('lists',$current_list['connection_id'],true);
$cash_admin->page_data['form_state_action'] = 'dolistedit';
$cash_admin->page_data['list_button_text'] = 'Edit the list';

$cash_admin->setPageContentTemplate('people_lists_details');
?>