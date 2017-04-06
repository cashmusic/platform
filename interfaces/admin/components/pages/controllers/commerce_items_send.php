<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$cash_admin->page_data['ui_title'] = '';
$cash_admin->page_data['id'] = $request_parameters[0];

$cash_admin->setPageContentTemplate('commerce_items_send');
?>
