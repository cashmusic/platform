<?php
// parsing posted data:
if (isset($_POST['dovenueedit'])) {
	$edit_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'calendar', 
			'cash_action' => 'editvenue',
			'venue_id' => $request_parameters[0],
			'name' => $_POST['venue_name'],
			'city' => $_POST['venue_city'],
			'region' => $_POST['venue_region'],
			'country' => $_POST['venue_country'],
			'address1' => $_POST['venue_address1'],
			'address2' => $_POST['venue_address2'],
			'postalcode' => $_POST['venue_postalcode'],
			'url' => $_POST['venue_url'],
			'phone' => $_POST['venue_phone']
		),
		'venueeditattempt'
	);
	if ($edit_response['status_uid'] == 'calendar_editvenue_200') {
		AdminHelper::formSuccess('Success. Edited.');
	} else {
		AdminHelper::formFailure('Error. There was a problem editing.');
	}
}

$current_venue_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'getvenue',
		'venue_id' => $request_parameters[0]
	),
	'getvenue'
);

$current_venue = $current_venue_response['payload'];
if (is_array($current_venue)) {
	$cash_admin->page_data = array_merge($cash_admin->page_data,$current_venue);
}

$cash_admin->page_data['form_state_action'] = 'dovenueedit';
$cash_admin->page_data['venue_button_text'] = 'Edit the venue';
$cash_admin->page_data['country_options'] = AdminHelper::drawCountryCodeUL($current_venue['country']);

$cash_admin->setPageContentTemplate('calendar_venues_details');
?>