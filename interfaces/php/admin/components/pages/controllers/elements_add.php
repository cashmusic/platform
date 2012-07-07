<?php
$page_request = new CASHRequest(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getsupportedtypes'
	)
);
$supported_elements = $page_request->response['payload'];
$elements_data = AdminHelper::getElementsData();

if ($request_parameters) {
	$element_addtype = $request_parameters[0];
	$cash_admin->page_data['form_state_action'] = 'doelementadd';
	$cash_admin->page_data['element_type'] = $element_addtype;
	if (isset($elements_data[$element_addtype])) {
		$cash_admin->page_data['ui_title'] = 'Elements: Add ' . $elements_data[$element_addtype]['name'] . ' Element';
		$cash_admin->page_data['ui_page_tip'] = $elements_data[$element_addtype]['pagetip'];
	}

	if (array_search($element_addtype, $supported_elements) !== false) {
		if (@file_exists(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_addtype . '/admin.php')) {
			include(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_addtype . '/admin.php');
			$cash_admin->page_data['element_button_text'] = 'Add the element';

			if ($cash_admin->getCurrentElementState() == 'add' && !$cash_admin->getErrorState()) {
				$current_element = $cash_admin->getCurrentElement();
				AdminHelper::controllerRedirect('/elements/edit/' . $current_element['id']);
			}
			$cash_admin->page_data['element_rendered_content'] = $cash_admin->mustache_groomer->render(file_get_contents(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_addtype . '/templates/admin.mustache'), $cash_admin->page_data);
		} else {
			$$cash_admin->page_data['element_rendered_content'] = "Could not find the admin.php file for this. Seriously, that element is broken like crazy.";
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
	foreach ($elements_data as $element => $data) {
		if (array_search($element, $supported_elements) !== false) {
			$formatted_element = array(
				'element_type' => $element,
				'element_type_name' => $data['name'],
				'element_type_description' => $data['description'],
				'element_type_longdescription' => $data['longdescription'],
				'element_type_author' => $data['author'],
				'element_type_authorurl' => $data['url'],
				'element_type_updated' => $data['lastupdated'],
				'element_type_version' => $data['version']
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
	}

	$cash_admin->page_data['elements_col1'] = new ArrayIterator($column1);
	$cash_admin->page_data['elements_col2'] = new ArrayIterator($column2);
	$cash_admin->page_data['elements_col3'] = new ArrayIterator($column3);

	$cash_admin->setPageContentTemplate('elements_add_select');
}
?>