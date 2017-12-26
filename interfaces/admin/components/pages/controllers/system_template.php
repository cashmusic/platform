<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_request, $cash_admin);

if (isset($_POST['dotemplateset'])) {
	// form was submitted. set the template
	$effective_user = $cash_admin->effective_user_id;
	if (!isset($_POST['template_id'])) {
		$template_id = false;
	} else {
		$template_id = $_POST['template_id'];
	}
	$template_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'settemplate',
			'name' => $_POST['template_name'],
			'template' => $_POST['template'],
			'template_id' => $template_id,
			'user_id' => $effective_user
		)
	);
	if ($template_response['payload']) {
		$admin_helper->formSuccess('Success.','/system/template/' . $template_response['payload']);
	} else {
		$admin_helper->formFailure('Error. Something just didn\'t work right.','/system/template/');
	}
}

if ($request_parameters[0]) {
	$effective_user = $cash_admin->effective_user_id;
	$template_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'gettemplate',
			'template_id' => $request_parameters[0],
			'all_details' => 1,
			'user_id' => $effective_user
		)
	);
	if (is_array($template_response['payload'])) {
		$cash_admin->page_data = array_merge($template_response['payload'],$cash_admin->page_data);
	}
} else {
	$cash_admin->page_data['template'] = file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/page.mustache');
}

$cash_admin->page_data['button_text'] = 'Save this template';

$cash_admin->setPageContentTemplate('system_template');
?>