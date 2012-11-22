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

	// for element redirects (payment, oauth, etc) ...yuck
	if (isset($_GET['cash_action']) && isset($_GET['element_id'])) {
		$requests = array('embed',$_GET['element_id']);
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
			header('P3P: CP="ALL CUR OUR"'); // IE P3P privacy policy fix
			$template = str_replace('</head>', '<script type="text/javascript" src="' . CASH_PUBLIC_URL . '/cashmusic.js"></script></head>', $template);
			// used this trick to grab cross-broser document height -> http://james.padolsey.com/javascript/get-document-height-cross-browser/
			$template = str_replace('</body>', '<script type="text/javascript">if(self!=top){var d = document;var docHeight = Math.max(Math.max(d.body.scrollHeight, d.documentElement.scrollHeight),Math.max(d.body.offsetHeight, d.documentElement.offsetHeight),Math.max(d.body.clientHeight, d.documentElement.clientHeight));window.parent.postMessage("cashmusic_embed_' . $requests[1] . '_" + docHeight, "*");var f = document.getElementsByTagName("form");for (var i = 0; i < f.length; i++) {var ee = document.createElement("input");ee.setAttribute("type", "hidden");ee.setAttribute("name", "embedded_element");ee.setAttribute("value", "1");f[i].appendChild(ee);}}</script></body>', $template);
			$encoded_html = $freddiemercury->render($template, $embed_data);
			echo $encoded_html;
		} else {
			header('Content-Type: text/html; charset=utf-8');
			if ($initial_page_request) {
				if (in_array('outputresponse', $requests)) {
					$output = $initial_page_request['response']['payload'];
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
			if (in_array('outputresponse', $requests)) {
				echo (string)$output;
			} else {
				echo json_encode($output);
			}
		}
	}
}
?>