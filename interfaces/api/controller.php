<?php
/**
 * The main API controller
 *
 * @package api.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2014, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */

namespace CASHMusic\API;

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');
require_once('constants.php');

use CASHMusic\Core\CASHAPI;

// push away anyone who's trying to access the controller directly
if (strrpos($_SERVER['REQUEST_URI'],'controller.php') !== false) {
	header($http_codes[403], true, 403);
	exit;
} else {
	return new CASHAPI();
}
?>
