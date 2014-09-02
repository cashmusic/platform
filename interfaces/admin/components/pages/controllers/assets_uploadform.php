<?php
$cash_admin->setPageContentTemplate('error');
if (isset($request_parameters[0])) {
	$param_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'getuploadparameters',
			'connection_id' => $request_parameters[0],
			'user_id' => $cash_admin->effective_user_id
		)
	);
	if (is_array($param_response['payload'])) {
		$cash_admin->page_data = array_merge($cash_admin->page_data,$param_response['payload']);
		$cash_admin->page_data['connection_id'] = $request_parameters[0];
		$which_form = str_replace('.','',$param_response['payload']['connection_type']);
		$cash_admin->setPageContentTemplate('assets_uploadform_' . $which_form);
	}
}
?>