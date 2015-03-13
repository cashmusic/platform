<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/');
}

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'deletecampaign',
			'id' => $request_parameters[0]
		)
	);
	if ($delete_response['status_uid'] == 'element_deletecampaign_200') {
		// get all campaigns 
		$campaigns_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getcampaignsforuser',
				'user_id' => $cash_admin->effective_user_id
			)
		);
		// if there's at least one remaining, select it
		if (count($campaigns_response['payload'])) {
			$current_campaign = $campaigns_response['payload'][count($campaigns_response['payload']) - 1]['id'];
			$admin_primary_cash_request->sessionSet('current_campaign',$current_campaign);
		
			$settings_request = new CASHRequest(
				array(
					'cash_request_type' => 'system', 
					'cash_action' => 'setsettings',
					'type' => 'selected_campaign',
					'value' => $current_campaign,
					'user_id' => $cash_admin->effective_user_id
				)
			);
		}

		if (isset($_REQUEST['redirectto'])) {
			AdminHelper::formSuccess('Success. Deleted.',$_REQUEST['redirectto']);
		} else {
			AdminHelper::formSuccess('Success. Deleted.','/elements/view/');
		}
	}
}
$cash_admin->page_data['title'] = 'Campaigns: Delete campaign';

$cash_admin->setPageContentTemplate('delete_confirm');
?>