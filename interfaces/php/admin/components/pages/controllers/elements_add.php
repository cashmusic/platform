<?php
$types_response = $cash_admin->requestAndStore(	
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getsupportedtypes'
	)
);
$supported_elements = $types_response['payload'];

if ($request_parameters) {
	$element_addtype = $request_parameters[0];
	$cash_admin->page_data['form_state_action'] = 'doelementadd';
	$cash_admin->page_data['element_type'] = $element_addtype;

	if (array_search($element_addtype, $supported_elements) !== false) {
		// Detects if element add has happened and deals with POST data if it has
		AdminHelper::handleElementFormPOST($_POST,$cash_admin);

		// Detects state of element add and routes to /elements/edit if successful
		if ($cash_admin->getCurrentElementState() == 'add' && !$cash_admin->getErrorState()) {
			$current_element = $cash_admin->getCurrentElement();
			AdminHelper::controllerRedirect('/elements/edit/' . $current_element['id']);
		}
		$app_json = AdminHelper::getElementAppJSON($element_addtype);
		if ($app_json) {

			// set page title/tip
			$cash_admin->page_data['ui_title'] = 'Add ' . $app_json['details']['en']['name'] . ' Element';
			$cash_admin->page_data['ui_page_tip'] = $app_json['copy']['en']['instructions'];

			foreach ($app_json['options'] as $section_name => $details) {
				foreach ($details['data'] as $data => $values) {
					if (isset($values['default']) && $values['type'] !== 'select') {
						if ($values['type'] == 'boolean') {
							if ($values['default']) {
								$default_val = true;
							}
						} else if ($values['type'] == 'number') {
							$default_val = $values['default'];
						} else {
							$default_val = $values['default']['en'];
						}
					}
					if ($values['type'] == 'select') {
						$default_val = AdminHelper::echoFormOptions(str_replace('/','_',$values['values']),0,false,true);
					}
					$cash_admin->page_data['options_' . $data] = $default_val;
				}
			}

			if (is_array($app_json['copy']['en'])) {
				foreach ($app_json['copy']['en'] as $key => $val) {
					$cash_admin->page_data['copy_' . $key] = $val;
				}
			}
			if (is_array($app_json['details']['en'])) {
				foreach ($app_json['details']['en'] as $key => $val) {
					$cash_admin->page_data['details_' . $key] = $val;
				}
			}

			$cash_admin->page_data['element_button_text'] = 'Add the element';
			$cash_admin->page_data['element_rendered_content'] = $cash_admin->mustache_groomer->render(AdminHelper::getElementTemplate($element_addtype), $cash_admin->page_data);
		}
	} else {
		$cash_admin->page_data['element_rendered_content'] = "You're trying to add an unsupported element. That's lame.";
	}

	$cash_admin->setPageContentTemplate('elements_add_selected');
} else {
	$elementcollection = array();

	$colcount = 1;
	foreach ($supported_elements as $element) {
		$app_json = AdminHelper::getElementAppJSON($element);
		$formatted_element = array(
			'element_type' => $element,
			'element_type_name' => $app_json['details']['en']['name'],
			'element_type_description' => $app_json['details']['en']['description'],
			'element_type_longdescription' => $app_json['details']['en']['longdescription'],
			'element_type_author' => $app_json['author'],
			'element_type_authorurl' => $app_json['url'],
			'element_type_updated' => $app_json['lastupdated'],
			'element_type_version' => $app_json['version']
		);

		$elementcollection[] = $formatted_element;
	
	}

	$cash_admin->page_data['elements_output'] = new ArrayIterator($elementcollection);

	$cash_admin->page_data['ui_title'] = 'Add an element';
	$cash_admin->setPageContentTemplate('elements_add_select');
}
?>