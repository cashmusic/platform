<?php
// add unique page settings:
$page_title = 'People: Main';
$page_tips = '';
$page_memu = array(
	'People' => array(
		'people/contacts/' => 'Contacts',
		'people/lists/' => 'Lists',
		'people/social/' => 'Social'
	)
);

$current_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlistsforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'getlistsforuser'
);

function people_format_lists($lists_response) {
	$markup = '';
	if ($lists_response['status_uid'] == "people_getlistsforuser_200") {
		// spit out the dates
		$markup .= '<ul class="alternating"> ';
		$loopcount = 1;
		foreach ($lists_response['payload'] as $list) {
			$altclass = '';
			if ($loopcount % 2 == 0) { $altclass = ' class="alternate"'; }
			$markup .= '<li' . $altclass . '> '
 					. '<h4>' . $list['name'] . '</h4>'
					. '<span class="altcopystyle">' . $list['description'] . '</span><br />'
					. '<div class="itemnav">'
					. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/view/' . $list['id'] . '" class="mininav_flush">View</a> '
					. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/view/' . $list['id'] . '" class="mininav_flush">Edit</a> '
					. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/view/' . $list['id'] . '" class="mininav_flush">Export</a> '
					. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/view/' . $list['id'] . '" class="mininav_flush needsconfirmation">Delete</a>'
					. '</div>';
			$markup .= '<div class="smalltext fadedtext created_mod">Created: ' . date('M jS, Y',$list['creation_date']); 
			if ($list['modification_date']) { 
				$markup .= ' (Modified: ' . date('F jS, Y',$list['modification_date']) . ')'; 
			}
			$markup .= '</div>';
			$loopcount = $loopcount + 1;
		}
		$markup .= '</ul>';
	} else {
		// no dates matched
		$markup .= 'No lists have been defined.';
	}
	return $markup;
}
?>