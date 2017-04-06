<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

if (!$request_parameters) {
	AdminHelper::controllerRedirect('/');
}

$current_element = $cash_admin->setCurrentElement($request_parameters[0]);
$cash_admin->page_data = array_merge($cash_admin->page_data,$current_element);

$cash_admin->page_data['ui_title'] = '“' . $current_element['name'] . '” (preview)';
$cash_admin->setPageContentTemplate('elements_preview');
?>