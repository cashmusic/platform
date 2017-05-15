<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$logout_request = new CASHRequest(null);
$logout_request->sessionClearAll();

//if (!isset($_REQUEST['noredirect'])) {
	AdminHelper::controllerRedirect('/');
//}
?>