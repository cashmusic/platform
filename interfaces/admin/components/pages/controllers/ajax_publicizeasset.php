<?php


namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;


if (isset($request_parameters[0])) {

    $success_response = $admin_request->request('asset')
                            ->action('makepublic')
                            ->with([
                                'id' => $request_parameters[0],
                                'commit' => true
                            ])->get();

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