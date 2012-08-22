<?php
if (!isset($request_parameters[0])) {
	echo json_encode(array("success" => false));
} else {
	$upload_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'addremoteuploadform',
			'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
			'connection_id' => $request_parameters[0]
		)
	);
	if ($upload_response['payload']) {
		$upload_response['payload']['success'] = true;
		echo json_encode($upload_response['payload']);
	} else {
		echo json_encode(array("success" => false));
	}
}
exit();
?>