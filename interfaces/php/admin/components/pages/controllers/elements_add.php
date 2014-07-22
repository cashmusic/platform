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
			$cash_admin->page_data['ui_page_tip'] = $app_json['copy']['en']['pagetip'];

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
			$cash_admin->page_data['element_button_text'] = 'Add the element';
			$cash_admin->page_data['element_rendered_content'] = $cash_admin->mustache_groomer->render(AdminHelper::getElementTemplate($element_addtype), $cash_admin->page_data);
		}
	} else {
		$cash_admin->page_data['element_rendered_content'] = "You're trying to add an unsupported element. That's lame.";
	}

	$cash_admin->setPageContentTemplate('elements_add_selected');
} else {
	$column1 = array();
	$column2 = array();
	$column3 = array();

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

		if ($colcount == 3) {
			$column3[] = $formatted_element;
			$colcount = 1;
		} elseif ($colcount == 2) {
			$column2[] = $formatted_element;
			$colcount++;
		} else {
			$column1[] = $formatted_element;
			$colcount++;
		}
	}

	$cash_admin->page_data['elements_col1'] = new ArrayIterator($column1);
	$cash_admin->page_data['elements_col2'] = new ArrayIterator($column2);
	$cash_admin->page_data['elements_col3'] = new ArrayIterator($column3);

	$cash_admin->setPageContentTemplate('elements_add_select');
}
?>