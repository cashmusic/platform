<?php
/**
 * CASH Music Test Uninstaller
 *
 * Cleans up after the test installer
 *
 * USAGE:
 * php tests/php/test-uninstaller.php
 * 
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
*/

if(!defined('STDIN')) { // force CLI, the browser is *so* 2007...
	echo "Please run uninstaller from the command line. usage:<br / >&gt; php tests/php/test-uninstaller.php";
} else {
	$installer_root = dirname(__FILE__);
	$repairs = 0;
	
	if (file_exists($installer_root . '/../../framework/db/cashmusic_test.sqlite.pretest.bak')) {
		rename($installer_root . '/../../framework/db/cashmusic_test.sqlite.pretest.bak',$installer_root . '/../../framework/db/cashmusic_test.sqlite');
		$repairs++;
	}
	if (file_exists($installer_root . '/../../framework/settings/cashmusic.ini.pretest.bak')) {
		rename($installer_root . '/../../framework/settings/cashmusic.ini.pretest.bak',$installer_root . '/../../framework/settings/cashmusic.ini.php');
		$repairs++;
	}
	if (file_exists($installer_root . '/../../tests/php/cookies.txt')) {
		unlink($installer_root . '/../../tests/php/cookies.txt');
		$repairs++;
	}
	
	if ($repairs) {
		echo "Put things back as they belong. Test INI file and database removed.\n";
	} else {
		echo "Test INI file and database were not removed.\n";
		exit(1);
	}
}
?>
