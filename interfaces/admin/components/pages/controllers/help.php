<?php
	$help_topics = json_decode(file_get_contents(ADMIN_BASE_PATH . '/components/text/en/help/topics.json'),true);
	
	$filtered_topics = array();
	foreach ($help_topics as $topic) {
		// check instance-type compatibility
		if (in_array($cash_admin->platform_type, $topic['compatibility'])) {
			$filtered_topics[] = array(
				'file' => $topic['file'],
				'title' => $topic['title']
			);
		}
	}

	if (!isset($request_parameters[0])) {
		$cash_admin->page_data['help_topics'] = new ArrayIterator($filtered_topics);
		$cash_admin->setPageContentTemplate('help_topics');
	} else {
		if (file_exists(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php')) {
   			include_once(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php');
   		}

		$cash_admin->page_data['ui_title'] = $help_topics[$request_parameters[0]]['title'];
		$cash_admin->page_data['topic_copy'] = Markdown(file_get_contents(ADMIN_BASE_PATH . '/components/text/en/help/' . $request_parameters[0] . '.md'));
		$cash_admin->setPageContentTemplate('help_topic');
	}
?>