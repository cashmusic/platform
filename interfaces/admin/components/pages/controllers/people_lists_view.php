<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_primary_cash_request, $cash_admin);

if ($request_parameters) {
	$request_list_id = $request_parameters[0];

	$current_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'getlist',
			'list_id' => $request_list_id
		)
	);

	if (is_object($current_response['payload'])) {
        $list = $current_response['payload']->toArray();
    }

	if ($list) {
		$cash_admin->page_data['ui_title'] = '' . $list['name'] . '';
		$cash_admin->page_data['list_description'] = $list['description'];
		$cash_admin->page_data['list_id'] = $request_list_id;

		$list_members = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'viewlist',
				'list_id' => $request_list_id
			)
		);
		if (is_array($list_members['payload']['members'])) {
			foreach ($list_members['payload']['members'] as &$entry) {
            // array stuff
            $entry = json_decode(json_encode($entry), true);

			$entry['formatted_date'] = date('M j, Y',$entry['creation_date']);
		}
		$cash_admin->page_data['list_members'] = new ArrayIterator($list_members['payload']['members']);
	} else {
		$cash_admin->page_data['list_members'] = false;
	}
		
		
		$list_analytics = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getanalytics',
				'analtyics_type' => 'listmembership',
				'list_id' => $request_list_id,
				'user_id' => $cash_admin->effective_user_id
			)
		);

        if (is_object($list_analytics['payload'])) {
            $list_analytics = $list_analytics['payload'];
        }
        
		$cash_admin->page_data['analytics_active'] = $list_analytics->active;
		$cash_admin->page_data['analytics_inactive'] = $list_analytics->inactive;
		$cash_admin->page_data['analytics_last_week'] = $list_analytics->last_week;
        $cash_admin->page_date['list_id'] = $request_list_id;

		$cash_admin->setPageContentTemplate('people_lists_view');

        if (!empty($_POST['status'])) {
            if ($_POST['status'] == "success") {
                $admin_helper->formSuccess('Success. Added '.$_POST['count'].' bulk emails from upload.');
            } else {
                $admin_helper->formFailure('Error. There was a problem importing your file.');
            }
        }

	} else {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/');	
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/');
}
?>