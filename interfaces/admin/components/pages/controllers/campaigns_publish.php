<?php
$campaigns_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getcampaignsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

if (is_array($campaigns_response['payload'])) {
	foreach ($campaigns_response['payload'] as &$campaign) {
		if ($campaign['modification_date'] == 0) {
			$campaign['formatted_date'] = CASHSystem::formatTimeAgo($campaign['creation_date']);	
		} else {
			$campaign['formatted_date'] = CASHSystem::formatTimeAgo($campaign['modification_date']);
		}
	}
	$cash_admin->page_data['campaigns_for_user'] = new ArrayIterator($campaigns_response['payload']);
} 

$cash_admin->page_data['ui_title'] = 'Publish a campaign';
$cash_admin->setPageContentTemplate('campaigns_publish');
?>