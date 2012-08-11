<?php
$effective_user = AdminHelper::getPersistentData('cash_effective_user');

if (isset($_POST['press_url'])) {
	// if at first it isn't valid, try adding 'http://'
	if (substr($_POST['press_url'], 0, 4) != 'http') {
    	$_POST['press_url'] = 'http://' . $_POST['press_url'];
	}

	// check proper validity 
	if (strpos($_POST['press_url'], '.') !== false) {
		// try getting the html
		$html = CASHSystem::getURLContents($_POST['press_url']);

		if ($html) {
			//parsing begins here:
			$doc = new DOMDocument();
			@$doc->loadHTML($html);
			$nodes = $doc->getElementsByTagName('title');

			//get and display what you need:
			$url_title = $nodes->item(0)->nodeValue . ' (' . str_replace('www.','',parse_url($_POST['press_url'],PHP_URL_HOST)) . ')';
			$metas = $doc->getElementsByTagName('meta');

			for ($i = 0; $i < $metas->length; $i++) {
				$meta = $metas->item($i);
				if($meta->getAttribute('name') == 'description')
				$url_description = $meta->getAttribute('content');
			}

			if ($_POST['publishing_date']) {
				$url_date = strtotime($_POST['publishing_date']);
			} else {
				$url_date = time();
			}
			$url_metadata = array(
				'publishing_date' => $url_date
			);

			$add_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'addasset',
					'title' => $url_title,
					'description' => $url_description,
					'user_id' => $effective_user,
					'location' => $_POST['press_url'],
					'metadata' => $url_metadata,
					'type' => 'system_people_presslink'
				)
			);

			if ($add_response['payload']) {
				AdminHelper::formSuccess('Success. New link added');
			} else {
				AdminHelper::formFailure('Error. Something just didn\'t work right.');
			}
		} else {
			$cash_admin->page_data['error_message'] = 'please enter a valid URL';		
		}
	} else {
		$cash_admin->page_data['error_message'] = 'please enter a valid URL';
	}
}


$links_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getassetsforuser',
		'type' => 'system_people_presslink',
		'parent_id' => 0,
		'user_id' => $effective_user
	)
);

if (is_array($links_response['payload'])) {
	$links_response['payload'] = array_reverse($links_response['payload']); // newest first
	foreach ($links_response['payload'] as &$link) {
		$link['descriptor_string'] = 'added: ' . CASHSystem::formatTimeAgo($link['creation_date']);
		$link['publishing_date'] = CASHSystem::formatTimeAgo($link['metadata']['publishing_date']);
	}
	$cash_admin->page_data['press_links'] = new ArrayIterator($links_response['payload']);
}

$cash_admin->page_data['current_date'] = date('m/d/Y');
$cash_admin->setPageContentTemplate('people_press');
?>