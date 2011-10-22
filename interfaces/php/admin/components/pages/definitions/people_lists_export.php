<?php
// action-only non-display page...since it's returning CSV it's not really part
// of the /api/ interface, so a bit of an exception to the rule...

$request_list_id = $request_parameters[0];

$list_details = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'viewlist',
		'list_id' => $request_list_id
	),
	'listdetails'
);
if (isset($list_details)) {
	//header('Content-Disposition: attachment; filename="list_' . $request_list_id . '_export.csv"');
	if ($list_details['status_uid'] == 'people_viewlist_200') {
		echo '"email address","display name","initial comment","additional data","join date"' . "\n";
		foreach ($list_details['payload']['members'] as $entry) {
		    echo '"' . str_replace ('"','""',$entry['email_address']) . '"';
			echo ',"' . str_replace ('"','""',$entry['display_name']) . '"';
			echo ',"' . str_replace ('"','""',$entry['initial_comment']) . '"';
			echo ',"' . str_replace ('"','""',$entry['additional_data']) . '"';
			echo ',"' . date('M j, Y h:iA T',$entry['creation_date']) . '"';
			echo "\n";
		}
	} else {
		echo "Error getting list.";
	}
}

exit;
?>