<?php


namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_primary_cash_request, $cash_admin);

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
		$admin_helper->formSuccess('Success. Edited.');
	} else {
		$admin_helper->formFailure('Error. There was a problem editing.');
	}
}

if (isset($_POST['dobatchcontactsadd'])) {
    if (!empty($_POST['element_content'])) {

        $email_array = CASHSystem::parseBulkEmailInput($_POST['element_content']);

        $total_added = 0;

        $add_response = $cash_admin->requestAndStore(
            array(
                'cash_request_type' => 'people',
                'cash_action' => 'addbulkaddresses',
                'do_not_verify' => 1,
                'address' => $email_array
            )
        );

        if ($add_response['payload']) {
           if (is_array($add_response['payload'])) {

               $created_user_ids = $add_response['payload'];
               $total_added = count($created_user_ids);

               $list_response = $cash_admin->requestAndStore(
                   array(
                       'cash_request_type' => 'people',
                       'cash_action' => 'addbulkaddresses',
                       'user_ids' => $created_user_ids,
                       'list_id' => $request_parameters[0]
                   )
               );
           }
        }

        dd($add_response);

        if ($total_added > 0 && $list_response['payload']) {
            $admin_helper->formSuccess('Success. Added '.$total_added." contacts.", '/people/lists/view/'.$request_parameters[0]);
        } else {
            $admin_helper->formFailure('Error. There was a problem adding contacts.', '/people/lists/view/'.$request_parameters[0]);
        }
    }
}


$current_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlist',
		'list_id' => $request_parameters[0]
	)
);

$current_list = $current_response['payload']->toArray();

$cash_admin->page_data['ui_title'] = '' . $current_list['name'] . '';

$cash_admin->page_data['no_selected_connection'] = true;
if (is_array($current_list)) {
	$cash_admin->page_data = array_merge($cash_admin->page_data,$current_list);
	if ($current_list['connection_id'] != 0) {
		$cash_admin->page_data['no_selected_connection'] = false;
	}
}
$cash_admin->page_data['connection_options'] = $admin_helper->echoConnectionsOptions('lists',$current_list['connection_id'],true);
$cash_admin->page_data['form_state_action'] = 'dolistedit';
$cash_admin->page_data['list_button_text'] = 'Save changes';

$cash_admin->page_data['list_id'] = $request_parameters[0];

$cash_admin->setPageContentTemplate('people_lists_batch_details');
?>