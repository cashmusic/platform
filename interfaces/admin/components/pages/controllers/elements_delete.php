<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_request, $cash_admin);

if (!$request_parameters) {
	AdminHelper::controllerRedirect('/elements/view/');
}

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'element',
			'cash_action' => 'deleteelement',
			'id' => $request_parameters[0]
		)
	);
	if ($delete_response['status_uid'] == 'element_deleteelement_200') {

		// look for the element in a campaign. if it's there, remove it.
		$campaign_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element',
				'cash_action' => 'getcampaignforelement',
				'id' => $request_parameters[0]
			)
		);
		if ($campaign_response['payload']) {
			$cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'element',
					'cash_action' => 'removeelementfromcampaign',
					'campaign_id' => $campaign_response['payload']['id'],
					'element_id' => $request_parameters[0]
				)
			);
			$admin_helper->formSuccess('Success. Deleted.','/elements/');
		}


		if (isset($_REQUEST['redirectto'])) {
			$admin_helper->formSuccess('Success. Deleted.',$_REQUEST['redirectto']);
		} else {
			$admin_helper->formSuccess('Success. Deleted.','/elements/view/');
		}
	}
}
$cash_admin->page_data['title'] = 'Elements: Delete element';

$cash_admin->setPageContentTemplate('delete_confirm');
?>
