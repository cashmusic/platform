<?php
// parsing posted data:
if (isset($_POST['dolistadd'])) {
	// do the actual list add stuffs...
	$effective_user = $cash_admin->effective_user_id;
	$add_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'addlist',
			'name' => $_POST['list_name'],
			'description' => $_POST['list_description'],
			'connection_id' => $_POST['connection_id'],
			'user_id' => $effective_user,
		)
	);
	if ($add_response['payload']) {
		AdminHelper::formSuccess('Success. List added.','/people/lists/edit/' . $add_response['payload']);
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/people/lists/add/');
	}
}

$cash_admin->page_data['connection_options'] = AdminHelper::echoConnectionsOptions('lists',0,true);
$cash_admin->page_data['form_state_action'] = 'dolistadd';
$cash_admin->page_data['list_button_text'] = 'Add the list';

$cash_admin->setPageContentTemplate('people_lists_details');
?>