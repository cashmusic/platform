<?php
if (isset($_REQUEST['modalconfirm'])) {
	$new_template = 0;
	$requested_campaign_id = $request_parameters[0];

	if ($requested_campaign_id != 0) {
		$current_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getcampaign',
				'id' => $requested_campaign_id
			)
		);
		$campaign = $current_response['payload'];
		$new_template = $campaign['template_id'];
	} 

	$settings_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'setsettings',
			'type' => 'public_profile_template',
			'value' => $new_template,
			'user_id' => $cash_admin->effective_user_id
		)
	);

	if ($settings_response['payload']) {
		if ($new_template == 0) {
			AdminHelper::formSuccess('Success. You have unpublished all campaigns.','/');
		} else {
			AdminHelper::formSuccess('Success. Campaign published.','/');
		}
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/');
	}
	
} else {
	AdminHelper::controllerRedirect('/');
}
?>