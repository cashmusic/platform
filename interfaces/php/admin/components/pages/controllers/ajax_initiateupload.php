<?php
$upload_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'addremoteuploadform',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
		'connection_id' => 1
	)
);

echo json_encode($upload_response['payload']);
exit();
?>