<?php
// parsing posted data:
if (isset($_POST['dolistedit'])) {
	// do the actual list add stuffs...
	$edit_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'editlist',
			'list_id' => $request_parameters[0],
			'name' => $_POST['list_name'],
			'description' => $_POST['list_description'],
			'connection_id' => $_POST['connection_id']
		)
	);
	if ($edit_response['status_uid'] == 'people_editlist_200') {
		AdminHelper::formSuccess('Success. Edited.');
	} else {
		AdminHelper::formFailure('Error. There was a problem editing.');
	}
}

if (isset($_POST['dobatchcontactsadd'])) {
	if (!empty($_POST['element_content'])) {
		$email_array = array_map('trim',explode(",",str_replace(PHP_EOL,',',$_POST['element_content'])));
		if (count($email_array) > 0) {
			$total_added = 0;
			foreach ($email_array as $address) {
				$add_response = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'people', 
						'cash_action' => 'addaddresstolist',
						'do_not_verify' => 1,
						'address' => $address,
						'list_id' => $request_parameters[0]
					)
				);
				if ($add_response['payload']) {
					$total_added++;
				}
			}
			$cash_admin->page_data['page_message'] = 'Success. Added ' . $total_added . ' new emails to your list.';
		} else {
			$cash_admin->page_data['error_message'] = 'Could not find any valid email addresses. Please try again.';
		}
	} else {
		$cash_admin->page_data['error_message'] = 'Error. Please try again.';
	}
}


$current_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlist',
		'list_id' => $request_parameters[0]
	)
);
$cash_admin->page_data['ui_title'] = 'People: Edit "' . $current_response['payload']['name'] . '"';

$current_list = $current_response['payload'];

$cash_admin->page_data['no_selected_connection'] = true;
if (is_array($current_list)) {
	$cash_admin->page_data = array_merge($cash_admin->page_data,$current_list);
	if ($current_list['connection_id'] != 0) {
		$cash_admin->page_data['no_selected_connection'] = false;
	}
}
$cash_admin->page_data['connection_options'] = AdminHelper::echoConnectionsOptions('lists',$current_list['connection_id'],true);
$cash_admin->page_data['form_state_action'] = 'dolistedit';
$cash_admin->page_data['list_button_text'] = 'Edit the list';

$cash_admin->setPageContentTemplate('people_lists_details');
?>