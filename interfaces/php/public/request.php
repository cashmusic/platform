<?php
require_once('./constants.php');
require_once(CASH_PLATFORM_PATH);

if (!isset($_REQUEST['nooutput'])) {
	$cash_page_request = new CASHRequest(null);
	$initial_page_request = $cash_page_request->sessionGet('initial_page_request','script');
	if ($initial_page_request) {
		if (isset($_REQUEST['outputresponse'])) {
			$output = $initial_page_request['response'];
		} else {
			$output = array(
				'response' => $initial_page_request['response']
			);
		}
	} else {
		$output = array(
			'response' => false
		);
	}
	if (isset($_REQUEST['outputresponse'])) {
		echo $output;
	} else {
		echo json_encode($output);
	}
}
?>