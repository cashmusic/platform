<?php
if (isset($request_parameters[0])) {
	$success_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'makepublic',
			'id' => $request_parameters[0]
		)
	);
	if ($success_response['payload']) {
		echo json_encode(array(
			'success' => true,
			'location' => $success_response['payload']
		));
	} else {
		echo '{"success":"false"}';
	}
} else {
	echo '{"success":"false"}';
}
exit();
?>