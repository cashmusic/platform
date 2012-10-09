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
			$embed_location = false;
			if (isset($requests[3])) {
				$embed_location = $requests[3];
				$embed_location = str_replace('!slash!', '/', $embed_location);
			}
			$template = @file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/embed.mustache');
			$embed_data = array();
			$element_markup = false;
			ob_start();
			CASHSystem::embedElement($requests[1],'embed',$embed_location);
			$embed_data['element_markup'] = ob_get_contents();
			ob_end_clean();

			// open up some mustache in here:
			include_once(dirname(CASH_PLATFORM_PATH) . '/lib/mustache/Mustache.php');
			$freddiemercury = new Mustache;
			$template = str_replace('</body>', '<script type="text/javascript">window.parent.postMessage(document.body.scrollHeight.toString(), "*");</script></body>', $template);
			$encoded_html = $freddiemercury->render($template, $embed_data);
			echo $encoded_html;
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