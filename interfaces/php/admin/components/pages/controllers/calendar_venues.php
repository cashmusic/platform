<?php
function formatVenueOutput(&$response) {
	foreach ($response['payload'] as &$venue) {
		// format location
		if (strtolower($venue['country']) == 'usa' || strtolower($venue['country']) == 'canada') {
			$venue['formatted_location'] = $venue['city'] . ', ' . $venue['region'];
		} else {
			$venue['formatted_location'] = $venue['city'] . ', ' . $venue['country'];	
		}
		if ($venue['formatted_location'] == ', ') {
			$venue['formatted_location'] = '';
		}
	}
}

$venues_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'getallvenues',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
		'visible_event_types' => 'upcoming'
	),
	'getallvenues'
);

if (is_array($venues_response['payload'])) {
	formatVenueOutput($venues_response);
	$cash_admin->page_data['all_venues'] = new ArrayIterator($venues_response['payload']);
}

$cash_admin->page_data['country_codes'] = AdminHelper::drawCountryCodeUL();

$cash_admin->setPageContentTemplate('calendar_venues');
?>