<?php


namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;


if (isset($_REQUEST['connection_id']) && isset($_REQUEST['filename'])) {
    
	$success_response = $admin_request->request('asset')
	                        ->action('finalizeupload')
	                        ->with([
                                'connection_id' => $_REQUEST['connection_id'],
                                'filename' => $_REQUEST['filename']
                            ])->get();
	if ($success_response['payload']) {
		echo '{"success":"true"}';
	} else {
		echo '{"success":"false"}';
	}
} else {
	echo '{"success":"false"}';
}
exit();
?>