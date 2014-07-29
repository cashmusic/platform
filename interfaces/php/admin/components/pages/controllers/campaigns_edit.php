<?php
// parsing posted data:
if (isset($_POST['docampaignedit'])) {
	// do the actual list add stuffs...
	$edit_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'editcampaign',
			'id' => $request_parameters[0],
			'title' => $_POST['campaign_title'],
			'description' => $_POST['campaign_description']
		)
	);
	if ($edit_response['status_uid'] == 'element_editcampaign_200') {
		AdminHelper::formSuccess('Success. Edited.');
	} else {
		AdminHelper::formFailure('Error. There was a problem editing.');
	}
}

$current_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getcampaign',
		'id' => $request_parameters[0]
	)
);
$cash_admin->page_data['ui_title'] = 'Campaigns: Edit "' . $current_response['payload']['title'] . '"';

$current_campaign = $current_response['payload'];

if (is_array($current_campaign)) {
	$cash_admin->page_data = array_merge($cash_admin->page_data,$current_campaign);
}
$cash_admin->page_data['form_state_action'] = 'docampaignedit';
$cash_admin->page_data['button_text'] = 'Save changes';

$cash_admin->setPageContentTemplate('campaign_details');
?>