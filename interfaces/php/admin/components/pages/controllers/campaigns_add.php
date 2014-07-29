<?php
// parsing posted data:
if (isset($_POST['docampaignadd'])) {
	// do the actual list add stuffs...
	$effective_user = $cash_admin->effective_user_id;
	$add_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'addcampaign',
			'title' => $_POST['campaign_title'],
			'description' => $_POST['campaign_description'],
			'user_id' => $effective_user
		)
	);
	if ($add_response['payload']) {
		AdminHelper::formSuccess('Success. Campaign added.','/campaigns/edit/' . $add_response['payload']);
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/campaigns/add/');
	}
}

$cash_admin->page_data['form_state_action'] = 'docampaignadd';
$cash_admin->page_data['button_text'] = 'Add the campaign';

$cash_admin->setPageContentTemplate('campaign_details');
?>