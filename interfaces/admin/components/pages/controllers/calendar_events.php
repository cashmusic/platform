<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_request, $cash_admin);

function formatEventOutput(&$response) {
    foreach ($response['payload'] as &$event) {
        // fix empty venue name
        if (!$event['venue_name']) {
            $event['venue_name'] = 'TBA';
        }
        // format date for viewing
        $event['formatted_date'] = date('d M',$event['date']);
        // format location
        if (strtolower($event['venue_country']) == 'usa' || strtolower($event['venue_country']) == 'canada') {
            $event['event_location'] = $event['venue_city'] . ', ' . $event['venue_region'];
        } else {
            $event['event_location'] = $event['venue_city'] . ', ' . $event['venue_country'];
        }
        if ($event['event_location'] == ', ') {
            $event['event_location'] = '';
        }
    }
}

$event = "";
// Archive events
$allpast_response = $admin_request->request('calendar')
                        ->action('getevents')
                        ->with([
                            'user_id' => $cash_admin->effective_user_id,
                            'published_status' => 1,
                            'visible_event_types' => 'archive'
                        ])->get();

if (is_array($allpast_response['payload'])) {
    formatEventOutput($allpast_response);

    $cash_admin->page_data['events_allpast'] = new ArrayIterator(array_reverse($allpast_response['payload']));
}


// Upcoming events
$allfuture_response = $admin_request->request('calendar')
                        ->action('getevents')
                        ->with([
                            'user_id' => $cash_admin->effective_user_id,
                            'published_status' => 1,
                            'visible_event_types' => 'upcoming'
                        ])->get();


if (is_array($allfuture_response['payload'])) {

    formatEventOutput($allfuture_response);

	$cash_admin->page_data['events_allfuture'] = new ArrayIterator($allfuture_response['payload']);
}

$cash_admin->page_data['options_venues'] = $admin_helper->echoFormOptions('venues',0,false,true);

//Is Event Published Page data
$cash_admin->page_data['published'] = isset($event['published']) ? $event['published'] : false;

$cash_admin->setPageContentTemplate('calendar_events');
?>
