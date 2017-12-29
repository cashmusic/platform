<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

function formatVenueOutput(&$response) {
	foreach ($response['payload'] as &$venue) {

	    $venue = $venue->toArray();
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

$venues_response = $admin_request->request('calendar')
                        ->action('getallvenues')
                        ->with([
                            'user_id' => $cash_admin->effective_user_id,
                            'visible_event_types' => 'upcoming'
                        ])->get();

if (is_array($venues_response['payload'])) {
	formatVenueOutput($venues_response);
	$cash_admin->page_data['all_venues'] = new ArrayIterator($venues_response['payload']);
}

$cash_admin->page_data['country_codes'] = AdminHelper::drawCountryCodeUL();

$cash_admin->setPageContentTemplate('calendar_venues');
?>