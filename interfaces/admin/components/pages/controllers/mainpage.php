<?php
/*******************************************************************************
 *
 * 1. GET USER INFO / URLS
 *
 * {{last_login}} 				(ago-style formatted last login)
 * {{user_page_uri}} 			(full user page url)
 * {{user_page_display_uri}}	(user page prettied up)
 * {{public_url}} 				(CASH Instance public url)
 *
 ******************************************************************************/

// get username and any user data
$user_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people',
		'cash_action' => 'getuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (is_array($user_response['payload'])) {
	$current_username = $user_response['payload']['username'];
	$current_userdata = $user_response['payload']['data'];
	if (is_array($current_userdata)) {
		if (isset($current_userdata['last_login'])) {
			$cash_admin->page_data['last_login'] = CASHSystem::formatTimeAgo((int)$current_userdata['last_login'],true);
		} else {
			$cash_admin->page_data['last_login'] = false;
		}
	}
}

// get page url
if (SUBDOMAIN_USERNAMES) {
	$cash_admin->page_data['user_page_uri'] = rtrim(str_replace('admin', '', CASH_ADMIN_URL),'/');
	$cash_admin->page_data['user_page_uri'] = str_replace('://','://' . $current_username . '.',$cash_admin->page_data['user_page_uri']);
} else {
	$cash_admin->page_data['user_page_uri'] = rtrim(str_replace('admin', $current_username, CASH_ADMIN_URL),'/');
}
$cash_admin->page_data['user_page_display_uri'] = str_replace(array('http://','https://'),'',$cash_admin->page_data['user_page_uri']);


/*******************************************************************************
 *
 * 2. ACTIVITY FEED CONTENTS
 *
 * {{delta_lists}} 				(list changes since last login)
 * 	{{name}}							(name of the list)
 * 	{{total}}						(total new people)
 * 	{{singular}}					(true: just 1 person, false: 2+ new people)
 * {{delta_orders}} 				(number of orders since last login)
 * {{delta_orders_singular}}	(true: just 1 order, false: 2+ new orders)
 * {{all_lists}}					(all lists with activity / stats)
 * 	{{name}}							(list name)
 * 	{{analytics_active}}			(active membership count)
 * 	{{analytics_inactive}}		(unsubscribed count)
 * 	{{analytics_last_week}}		(subscribers in the last week)
 * {{unfulfilled_orders}}		(total unfulfilled orders OR false)
 *
 ******************************************************************************/

// GET ACTIVITY FOR LISTS AND ORDERS
$activity_request = new CASHRequest(
	array(
		'cash_request_type' => 'people',
		'cash_action' => 'getrecentactivity',
		'user_id' => $cash_admin->effective_user_id,
		'since_date' => $current_userdata['last_login']
	)
);
$activity = $activity_request->response['payload'];

// PARSE ACTIVITY FOR LISTS
$cash_admin->page_data['delta_lists'] = false;
if (is_array($activity['lists'])) {
	foreach ($activity['lists'] as &$list_stats) {
		if ($list_stats['total'] == 1) {
			$list_stats['singular'] = true;
		} else {
			$list_stats['singular'] = false;
		}
	}
	$cash_admin->page_data['delta_lists'] = $activity['lists'];
	error_log(json_encode($cash_admin->page_data['delta_lists']));
}

// PARSE ACTIVITY FOR ORDERS
if ($activity['orders']) {
	$cash_admin->page_data['delta_orders'] = count($activity['orders']);
	if ($cash_admin->page_data['delta_orders'] == 1) {
		$cash_admin->page_data['delta_orders_singular'] = true;
	}
} else {
	$cash_admin->page_data['delta_orders'] = false;
}

// GET ALL LISTS AND MEMBERSHIP STATS
$cash_admin->page_data['all_lists'] = false;
$list_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people',
		'cash_action' => 'getlistsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (is_array($list_response['payload'])) {
	foreach ($list_response['payload'] as &$list) {
		$list_analytics = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'people',
				'cash_action' => 'getanalytics',
				'analtyics_type' => 'listmembership',
				'list_id' => $list['id'],
				'user_id' => $cash_admin->effective_user_id
			)
		);
		$list['analytics_active'] = CASHSystem::formatCount($list_analytics['payload']['active']);
		$list['analytics_inactive'] = CASHSystem::formatCount($list_analytics['payload']['inactive']);
		$list['analytics_last_week'] = CASHSystem::formatCount($list_analytics['payload']['last_week']);
	}
	$cash_admin->page_data['all_lists'] = $list_response['payload'];
}

// FIND UNFULFILLED ORDERS
$cash_admin->page_data['unfulfilled_orders'] = false;
$orders_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce',
		'cash_action' => 'getordersforuser',
		'user_id' => $cash_admin->effective_user_id,
		'unfulfilled_only' => 1,
		'deep' => true
	)
);
if (is_array($orders_response)) {
	//error_log(json_encode($orders_response));
	$cash_admin->page_data['unfulfilled_orders'] = count($orders_response['payload']);
}

/*******************************************************************************
 *
 * 3. TOUR AND SPECIAL CASE BOOLEANS
 *
 * {{show_tour}} 					(should we show them a tour button?)
 * {{show_commerce_settings}} (should we show region/currency settings?)
 * {{nothing_at_all}}			(tumbleweeds up in here)
 *
 ******************************************************************************/

// we'll check for elements, and show the tour if none are present
$cash_admin->page_data['show_tour'] = false;
$cash_admin->page_data['first_use'] = false;
$elements_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element',
		'cash_action' => 'getelementsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (!$elements_response['payload']) {
	$cash_admin->page_data['show_tour'] = true;
	$cash_admin->page_data['first_use'] = true;
}

// SHOULD WE SHOW A WELCOME BACK MESSAGE?
$r = new CASHRequest();
$r->startSession();
$whatsnew = $r->sessionGet('whatsnew');
if (!$whatsnew) {
	$settings_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'getsettings',
			'type' => 'showafnews',
			'user_id' => $cash_admin->effective_user_id
		)
	);
	if (!$settings_response['payload']) {
		if (!$cash_admin->page_data['first_use']) {
			$cash_admin->page_data['show_whatsnew'] = true;
			$r->sessionSet('whatsnew', true);
		}
		// set this for later. we'll be able to reuse.
		$settings_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'setsettings',
				'type' => 'showafnews',
				'value' => time(),
				'user_id' => $cash_admin->effective_user_id
			)
		);
	}
} else {
	$cash_admin->page_data['show_whatsnew'] = true;
}


// what about regions, currency, and commerce?
$connections = AdminHelper::getConnectionsByScope('commerce');
if ($connections) {
	$settings_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'getsettings',
			'type' => 'regions',
			'user_id' => $cash_admin->effective_user_id
		)
	);
	if (!$settings_response['payload']) {
		$cash_admin->page_data['show_commerce_settings'] = true;
	}
}

// first use, or just really boring?
if (!$cash_admin->page_data['first_use']) {
	if (!$cash_admin->page_data['unfulfilled_orders']
		 //&& !$cash_admin->page_data['all_lists']
		 && !$cash_admin->page_data['delta_orders']
		 && !$cash_admin->page_data['delta_lists']
	) {
		$cash_admin->page_data['nothing_at_all'] = true;
	}
}

/*******************************************************************************
 *
 * 4. SET THE TEMPLATE AND GO!
 *
 ******************************************************************************/
$cash_admin->setPageContentTemplate('mainpage');
?>
