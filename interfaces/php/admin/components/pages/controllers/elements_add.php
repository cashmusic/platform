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
		$cash_admin->page_data['title'] = 'Elements: Add ' . $elements_data[$element_addtype]->name . ' Element';
	}

	if (array_search($element_addtype, $supported_elements) !== false) {
		if (@file_exists(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_addtype . '/admin.php')) {
			include(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_addtype . '/admin.php');
			$cash_admin->page_data['element_button_text'] = 'Add The Element';
			//$page_tips = $elements_data[$element_addtype]->pagetip;

			if ($cash_admin->getCurrentElementState() == 'add' && !$cash_admin->getErrorState()) {
				$current_element = $cash_admin->getCurrentElement();
				header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/edit/' . $current_element['id']);
			}
			$element_rendered_content = $cash_admin->mustache_groomer->render(file_get_contents(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_addtype . '/templates/admin.mustache'), $cash_admin->page_data);
		} else {
			$element_rendered_content = "Could not find the admin.php file for this .";
		}
	} else {
		$element_rendered_content = "You're trying to add an unsupported element. That's lame.";
	}
} else {
	$elements_sorted = array(
		'col1' => array(),
		'col2' => array(),
		'col3' => array()
	);
	$colcount = 1;
	foreach ($elements_data as $element => $data) {
		if (array_search($element, $supported_elements) !== false) {
			if ($colcount == 3) {
				$elements_sorted['col3'][$element] = $data;
				$colcount = 1;
			} elseif ($colcount == 2) {
				$elements_sorted['col2'][$element] = $data;
				$colcount++;
			} else {
				$elements_sorted['col1'][$element] = $data;
				$colcount++;
			}
		}
	}

	function drawFeaturedElement($element,$data) {
		echo '<div class="featuredelement">';
		echo '<a href="' . ADMIN_WWW_BASE_PATH . '/elements/add/' . $element . '"><img src="' . ADMIN_WWW_BASE_PATH . '/assets/images/elementheader.php?element=' . $element . '" width="100%" alt="' .  $data->name . '" /></a><br />';
		echo '<div class="padding">';
		echo '<h3>' . $data->name . '</h3>';
		echo '<p>' . $data->description . '</p>';
		echo '<div class="elementdetails"><p><span class="altcopystyle">' . $data->longdescription . '</span></p><small>Author: <a href="' . $data->url . '">' . $data->author . '</a><br />Last updated: ' . $data->lastupdated . '<br />Version: ' . $data->version . '</small></div>';
		echo '<div class="itemnav"><a href="' . ADMIN_WWW_BASE_PATH . '/elements/add/' . $element . '"><span class="icon plus_alt"></span> Add this now</a><br /><small><a href="' . $element . '" class="fadedtext showelementdetails"><span class="icon magnifying_glass"></span> More details</a></small></div>';
		echo '</div>';
		echo '</div>';
	}
}
?>