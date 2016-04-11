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
		// make the new campaign selected
		$admin_primary_cash_request->sessionSet('current_campaign',$add_response['payload']);

		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'setsettings',
				'type' => 'selected_campaign',
				'value' => $add_response['payload'],
				'user_id' => $cash_admin->effective_user_id
			)
		);

		AdminHelper::formSuccess('Success. Campaign added.','/elements/');
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/campaigns/add/');
	}
}

$cash_admin->page_data['form_state_action'] = 'docampaignadd';
$cash_admin->page_data['button_text'] = 'Save changes';

$cash_admin->setPageContentTemplate('campaign_edit');
?>
