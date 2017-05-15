<?php


namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;


if (isset($request_parameters[0])) {
	$success_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'makepublic',
			'id' => $request_parameters[0],
            'commit' => true
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