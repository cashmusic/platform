<?php
if (isset($request_parameters[0])) {
	$param_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'getuploadparameters',
			'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
			'connection_id' => $request_parameters[0]
		)
	);
	if (is_array($param_response['payload'])) {
		$cash_admin->page_data = array_merge($cash_admin->page_data,$param_response['payload']);
	}
}
$cash_admin->setPageContentTemplate('assets_uploadform');
?>