<?php
if (!isset($_REQUEST['nooutput'])) {
	$requests = false;
	if (isset($_GET['p'])) {
		if (strpos(trim($_GET['p'],'/'),'/')) {
			$requests = explode('/', trim($_GET['p'],'/'));
		} else {
			$requests = array(trim($_GET['p'],'/'));
		}
	}

	if ($requests) {
		require_once('./constants.php');
		require_once(CASH_PLATFORM_PATH);

		$cash_page_request = new CASHRequest(null);
		$initial_page_request = $cash_page_request->sessionGet('initial_page_request','script');

		if ($requests[0] == 'embed' && isset($requests[1])) {
			$element_markup = false;
			ob_start();
			CASHSystem::embedElement(1);
			$element_markup = ob_get_contents();
			ob_end_clean();
			echo $element_markup;
		} else {
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
	}
}
?>