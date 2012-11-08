<?php
/**
 *
 * This is the framework bootstrap script. It preps the environment (strips out 
 * stupid shit like magic quotes), includes required classes, and instantiates
 * a CASH request ready to use — pre-populated with any REQUEST data that may
 * have been passed to the page. 
 *
 * (Usage: included at the top of all pages.)
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/

if (isset($_SERVER['REQUEST_URI'])) {
	if(strrpos($_SERVER['REQUEST_URI'],'cashmusic.php') !== false) {
		header('Location: /');
		exit;
	}
}

require_once('classes/core/CASHSystem.php');
CASHSystem::startUp();
?>