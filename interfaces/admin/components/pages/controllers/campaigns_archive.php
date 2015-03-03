<?php
$campaign_id = 0;

$cash_admin->page_data['isarchive'] = true;
$cash_admin->page_data['title'] = 'Archived elements';
$cash_admin->page_data['description'] = 'This is a special campaign that stores stray elements from deleted campaigns.';
$cash_admin->page_data['formatted_date'] = 'Since forever';

/*
 *
 * 1. get all elements / count
 * 2. get all campaigns, merge element arrays / count
 * 3. elements - campaign elements != 0 then filter OUT campaign elements
 $ 4. show remaining elements
 *
 */

 // all user elements defined
$elements_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getelementsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (!is_array($elements_response['payload'])) {
	$elements_response['payload'] = array();
}

// get campaigns
$campaigns_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getcampaignsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
$campaign_elements = array();
if (is_array($campaigns_response['payload'])) {
	foreach ($campaigns_response['payload'] as $campaign) {
		$campaign['elements'] = json_decode($campaign['elements'],true);
		if (is_array($campaign['elements'])) {
			$campaign_elements = array_merge($campaign['elements'],$campaign_elements);
		}
	}
}

$extra_elements = count($elements_response['payload']) - count($campaign_elements);

if ($extra_elements !== 0) {
	$elements_for_campaign = array();
	foreach ($elements_response['payload'] as $element) {
		if (!in_array($element['id'], $campaign_elements)) {
			if ($element['modification_date'] == 0) {
				$element['formatted_date'] = CASHSystem::formatTimeAgo($element['creation_date']);	
			} else {
				$element['formatted_date'] = CASHSystem::formatTimeAgo($element['modification_date']);
			}
			$elements_for_campaign[] = $element;
		}
	}
	$cash_admin->page_data['elements_for_campaign'] = new ArrayIterator($elements_for_campaign);
}

$cash_admin->setPageContentTemplate('campaign_archive');
?>