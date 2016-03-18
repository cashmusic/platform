<?php
// form submit after wizard
if (isset($_POST['setpage'])) {
	$new_template = 0;

	$template_default = file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/page.mustache');
	$replacement = '';
	if (isset($_POST['element_id'])) {
		if ($_POST['element_id'] != 0) {
			$element_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'element',
					'cash_action' => 'getelement',
					'id' => $_POST['element_id']
				)
			);

			$replacement = '<!-- ' . $element_response['payload']['name'] . " -->\n\t\t{{{element_" . $_POST['element_id'] . '}}}';
		}
	}
	if (isset($_POST['pagetheme'])) {
		if ($_POST['pagetheme'] == 'light') {
			$template_default = str_replace('<body', '<body class="light"', $template_default);
		} else if ($_POST['pagetheme'] == 'dark') {
			$template_default = str_replace('<body', '<body class="dark"', $template_default);
		}
	}
	$template_default = str_replace('{{{element_n}}}',$replacement, $template_default);

	$template_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'settemplate',
			'name' => '',
			'type' => 'page',
			'template' => $template_default,
			'user_id' => $cash_admin->effective_user_id
		)
	);
	$template_id = $template_response['payload'];

	if ($template_id) {
		$edit_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'setsettings',
				'type' => 'primary_template_id',
				'value' => $template_id,
				'user_id' => $cash_admin->effective_user_id
			)
		);
	} else {
		AdminHelper::formFailure('Error. Could not create page.','/page/');
	}

	if ($edit_response['payload']) {
		AdminHelper::formSuccess('Success. Page created. You can edit it any time and publish when you are ready.','/page/');
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/page/');
	}

} else {
	// you're a wizard now, harry!
	$new_template = 0;

	$elements_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'element',
			'cash_action' => 'getelementsforuser',
			'id' => $cash_admin->effective_user_id
		)
	);

	if ( is_array($elements_response['payload']) ) {
		$elements = new ArrayIterator($elements_response['payload']);
		$cash_admin->page_data['has_elements'] = true;
	} else {
		$elements = false;
	}
	$cash_admin->page_data['elements'] = $elements;

	$cash_admin->page_data['ui_title'] = 'Create a page';
	$cash_admin->setPageContentTemplate('page_theme');
}
?>
