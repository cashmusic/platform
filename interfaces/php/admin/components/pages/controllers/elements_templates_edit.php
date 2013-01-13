<?php
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
		if ($template_response['payload']['type'] == 'embed') {
			$cash_admin->page_data['is_embed'] = true;
		}
	}
}

$cash_admin->page_data['button_text'] = 'Save changes';

$cash_admin->setPageContentTemplate('elements_template_details');
?>