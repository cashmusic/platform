<?php
$request_list_id = $request_parameters[0];

$list_details = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people',
		'cash_action' => 'viewlist',
		'list_id' => $request_list_id,
		'user_id' => $cash_admin->effective_user_id,
		'unlimited' => true
	)
);
if (is_array($list_details)) {
	header('Content-Disposition: attachment; filename="list_' . $request_list_id . '_export.csv"');
	if ($list_details['status_uid'] == 'people_viewlist_200') {
		echo '"email address","display name","first name","last name","initial comment","additional data","verified","active","join date"' . "\n";
		foreach ($list_details['payload']['members'] as $entry) {
		   echo '"' . str_replace ('"','""',$entry['email_address']) . '"';
			echo ',"' . str_replace ('"','""',$entry['display_name']) . '"';
			echo ',"' . str_replace ('"','""',$entry['first_name']) . '"';
			echo ',"' . str_replace ('"','""',$entry['last_name']) . '"';
			echo ',"' . str_replace ('"','""',$entry['initial_comment']) . '"';
			echo ',"' . str_replace ('"','""',$entry['additional_data']) . '"';
			echo ',"' . str_replace ('"','""',$entry['verified']) . '"';
			echo ',"' . str_replace ('"','""',$entry['active']) . '"';
			echo ',"' . date('M j, Y h:iA T',$entry['creation_date']) . '"';
			echo "\n";
		}
	} else {
		echo "Error getting list.";
	}
}

exit;
?>
