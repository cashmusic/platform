<?php
// add unique page settings:
$page_title = 'Calendar: Edit Venue';
$page_tips = 'Edit the venue.';

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'getvenue',
		'id' => $request_parameters[0]
	),
	'getvenue'
);

// parsing posted data:
if (isset($_POST['dovenueedit'])) {
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'calendar', 
			'cash_action' => 'editvenue',
			'id' => $request_parameters[0],
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
	
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'calendar', 
			'cash_action' => 'getvenue',
			'id' => $request_parameters[0]
		),
		'getvenue'
	);
}
?>