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
		$campaign['formatted_date'] = CASHSystem::formatTimeAgo($campaign['modification_date']);
	}
	$cash_admin->page_data['campaigns_for_user'] = new ArrayIterator($campaigns_response['payload']);
} 

$cash_admin->setPageContentTemplate('campaigns');
?>