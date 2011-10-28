<?php
// add unique page settings:
$page_title = 'Calendar: Add Venue';
$page_tips = "More coming soon.";

// parsing posted data:
if (isset($_POST['dovenueadd'])) {
	// do the actual list add stuffs...
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'calendar', 
			'cash_action' => 'addvenue',
			'name' => $_POST['venue_name'],
			'city' => $_POST['venue_city'],
			'region' => $_POST['venue_region'],
			'country' => $_POST['venue_country']
		),
		'venueaddattempt'
	);
}
?>