<?php
// add unique page settings:
$page_title = 'Calendar: Add Venue';
$page_tips = "More coming soon.";

// parsing posted data:
if (isset($_POST['dovenueadd'])) {
	// do the actual list add stuffs...
	$addvenue_address1 = '';
	$addvenue_address2 = '';
	$addvenue_postalcode = '';
	$addvenue_url = '';
	$addvenue_phone = '';
	if (isset($_POST['venue_address1'])) { $addvenue_address1 = $_POST['venue_address1']; }
	if (isset($_POST['venue_address2'])) { $addvenue_address2 = $_POST['venue_address2']; }
	if (isset($_POST['venue_postalcode'])) { $addvenue_postalcode = $_POST['venue_postalcode']; }
	if (isset($_POST['venue_url'])) { $addvenue_url = $_POST['venue_url']; }
	if (isset($_POST['venue_phone'])) { $addvenue_phone = $_POST['venue_phone']; }
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'calendar', 
			'cash_action' => 'addvenue',
			'name' => $_POST['venue_name'],
			'city' => $_POST['venue_city'],
			'region' => $_POST['venue_region'],
			'country' => $_POST['venue_country'],
			'address1' => $addvenue_address1,
			'address2' => $addvenue_address2,
			'postalcode' => $addvenue_postalcode,
			'url' => $addvenue_url,
			'phone' => $addvenue_phone
		),
		'venueaddattempt'
	);
}
?>