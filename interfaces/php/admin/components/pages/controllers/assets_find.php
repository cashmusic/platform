<?php
	if (isset($_POST['query'])) {
		AdminHelper::controllerRedirect('/assets/find/' . urlencode($_POST['query']) . '/1');
	}

	$cash_admin->setPageContentTemplate('assets_find');

	if ($request_parameters[0]) {
		$query = $request_parameters[0];
		if (isset($request_parameters[1])) {
			$page_number = $request_parameters[1];
		} else {
			$page_number = 1;
		}
		$query_length = strlen(trim($query));
		// only accept queries of 2+ characters
		if ($query_length > 1) {
			$effective_user = $cash_admin->effective_user_id;
			$find_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'findassets',
					'query' => $query,
					'user_id' => $effective_user,
					'page' => $page_number
				)
			);

			if ($find_response['payload']) {
				foreach ($find_response['payload'] as &$asset) {
					$asset['descriptor_string'] = $asset['type'];
					$asset['descriptor_string'] .= ' / created: ' . CASHSystem::formatTimeAgo($asset['creation_date']);
					if ($asset['modification_date']) {
						$asset['descriptor_string'] .= ' / last edited: ' . CASHSystem::formatTimeAgo($asset['modification_date']);
					}
					$regex_match = '/(' . preg_quote(htmlspecialchars($query)) . ')/i';
					$asset['title'] = preg_replace($regex_match, '<b>$1</b>', htmlspecialchars($asset['title']));
					$asset['description'] = preg_replace($regex_match, '<b>$1</b>', htmlspecialchars($asset['description']));
				}
				$cash_admin->page_data['current_query']	= $request_parameters[0];
				if ($page_number > 1) {
					$cash_admin->page_data['prev_page']	= $page_number - 1;
				}
				if (count($find_response['payload']) == 10) {
					$cash_admin->page_data['next_page']	= $page_number + 1;
				}
				$cash_admin->page_data['found_assets'] = new ArrayIterator($find_response['payload']);
				$cash_admin->page_data['ui_title'] = 'Assets: Matching “'. htmlspecialchars($request_parameters[0]) . '”';
				$cash_admin->setPageContentTemplate('assets_find_results');
			} else {
				$cash_admin->page_data['find_message'] = 'No matching assets found.';
			}
		} else {
			$cash_admin->page_data['find_message'] = 'Please enter a valid search query.';
		}
	}
?>