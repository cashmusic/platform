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

	/*
		Dear Jesse in the future,

		Don't fucking remove this. You think you want to. You feel you should.
		But remember that time you removed this and broke commerce for half a day?
		Yeah? You do? WELL THAT WAS BECAUSE YOU REMOVED THIS LITTLE BLOCK, JERK.

		Could you implement it better? Sure. Totally.

		Should you? Definitely. Wrap the direct JSON/payload stuff into the API
		proper. That's better than this half-assery.

		But should you delete this block?

		OF FUCKING COURSE YOU SHOULD NOT DELETE THIS BLOCK!

		Love,

		Jesse in the past
		AKA "Smart Jesse"

		PS: you're dumb.
	*/
	if (isset($_GET['cash_action']) && isset($_GET['element_id'])) {
		$requests = array('embed',$_GET['element_id']);
	}

	if ($requests) {
		require_once(dirname(__FILE__) . '/constants.php');
		require_once(CASH_PLATFORM_PATH);

		$cash_page_request = new CASHRequest(null);
		$initial_page_request = $cash_page_request->sessionGet('initial_page_request','script');

		if ($requests[0] != 'payload' || $requests[0] != 'json') {
			// open up some mustache in here:
			include_once(dirname(CASH_PLATFORM_PATH) . '/lib/mustache/Mustache.php');
			$freddiemercury = new Mustache;
		}

		// pass basic no-cache headers
		header('P3P: CP="ALL CUR OUR"'); // P3P privacy policy fix
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		header("Access-Control-Allow-Origin: *");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
      header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');

		if ($requests[0] == 'embed' && isset($requests[1])) {
			$embed_location = false;
			$embed_geo = false;
			if (isset($requests[3])) {
				$embed_location = $requests[3];
				$embed_location = str_replace('!slash!', '/', $embed_location); // dumb. supporting old versions.
			}
			if (isset($_GET['location'])) {
				$embed_location = $_GET['location'];
			}
			if (isset($_GET['geo'])) {
				$embed_geo = $_GET['geo'];
			}

			$template_request = new CASHRequest(
				array(
					'cash_request_type' => 'element',
					'cash_action' => 'getelementtemplate',
					'element_id' => $requests[1],
					'return_template' => 1
				)
			);
			$template = $template_request->response['payload'];

			$embed_data = array();
			$element_markup = false;
			ob_start();
			CASHSystem::embedElement($requests[1],'embed',$embed_location,$embed_geo);
			$embed_data['element_markup'] = ob_get_contents();
			$embed_data['cdn_url'] = (defined('CDN_URL')) ? CDN_URL : CASH_ADMIN_URL;

			ob_end_clean();

			header('Content-Type: text/html; charset=utf-8');
			$template = str_replace('</head>', '<script type="text/javascript" src="' . CASH_PUBLIC_URL . '/cashmusic.js"></script></head>', $template);
			$encoded_html = $freddiemercury->render($template, $embed_data);
			echo $encoded_html;
		} else {
			if ($initial_page_request) {
				if (in_array('payload', $requests)) {
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
			if (in_array('payload', $requests)) {
				header('Content-Type: text/plain; charset=utf-8');
				if (is_array($output)) {
					echo json_encode($output);
				} else {
					echo (string)$output;
				}
			} else if (in_array('json', $requests)) {
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode($output);
			} else {
				header('Content-Type: text/html; charset=utf-8');
				$template = file_get_contents(dirname(__FILE__) . '/templates/system.mustache');
				if (isset($output['response'])) {
					$embed_data = $output['response'];
				} else {
					$embed_data = array(
						'action' => 'Request not found',
						'contextual_message' => 'There was an error processing your request.'
					);
				}
				$encoded_html = $freddiemercury->render($template, $embed_data);
				echo $encoded_html;
			}
		}
	}
}
?>
