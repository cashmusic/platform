<?php
$list_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlistsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

// lists
if (is_array($list_response['payload'])) {
	$cash_admin->page_data['lists_all'] = new ArrayIterator($list_response['payload']);
}

$user_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (is_array($user_response['payload'])) {
	$current_userdata = $user_response['payload']['data'];
}

$session_news = AdminHelper::getActivity($current_userdata);
if ($session_news) {
	// now set up page variables
	$total_new = false;
	if (is_array($session_news['activity']['lists'])) {
		$total_new = false;
		foreach ($session_news['activity']['lists'] as &$list_stats) {
			$total_new = $total_new + $list_stats['total'];
			if ($list_stats['total'] == 1) {
				$list_stats['singular'] = true;
			} else {
				$list_stats['singular'] = false;
			}
		}
		if ($total_new == 1) {
			$cash_admin->page_data['people_singular'] = true;
		} else {
			$cash_admin->page_data['people_singular'] = false;
		}
	}
	$cash_admin->page_data['dashboard_list_total_new'] = $total_new;
	$cash_admin->page_data['dashboard_lists'] = $session_news['activity']['lists'];	
}


$cash_admin->page_data['current_date'] = date('m/d/Y');
$cash_admin->setPageContentTemplate('people');
?>