<?php
/**
 * CASH Music Test Installer
 *
 * Takes the CASH DIY repo and sets it up  as a working platform instance so
 * tests can be run against it.
 *
 * USAGE:
 * php installers/php/test_installer.php
 * 
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
*/

if(!defined('STDIN')) { // force CLI, the browser is *so* 2007...
	echo "Please run installer from the command line. usage:<br / >&gt; php installers/php/test_installer.php";
} else {
	function findReplaceInFile($filename,$find,$replace) {
		if (is_file($filename)) {
			$file = file_get_contents($filename);
			$file = str_replace($find, $replace, $file);
			if (file_put_contents($filename, $file)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	$success = false;
	echo "\nCASH MUSIC PLATFORM TEST INSTALLER\nYou are in an open field west of a big white house with a boarded front door.\n\n";
	$db_engine = 'sqlite';
	$installer_root = dirname(__FILE__);

	// if the file exists already, rename it as a backup
	if (file_exists($installer_root . '/../../framework/db/cashmusic_test.sqlite')) {
		rename($installer_root . '/../../framework/db/cashmusic_test.sqlite',$installer_root . '/../../framework/db/cashmusic_test.sqlite.bak');
	} else {
		// if the directory was never created then create it now
		if (!file_exists($installer_root . '/../../framework/db')) {
			mkdir($installer_root . '/../../framework/db');
		}
	}
	
	// connect to the new db...will create if not found
	try {
		$pdo = new PDO ('sqlite:' . $installer_root . '/../../framework/db/cashmusic_test.sqlite');
		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	} catch (PDOException $e) {
		echo "\nOh. Shit. Something's wrong: Couldn't connect to the database. $e\n\n";
		die();
		break;
	}
	// TODO: Suboptimal
	if ($pdo) {
		chmod($installer_root . '/../../framework/db',0777);
		chmod($installer_root . '/../../framework/db/cashmusic_test.sqlite',0777);
	}
	
	// push in all the tables
	try {
		$pdo->exec(file_get_contents($installer_root . '/../../framework/php/settings/sql/cashmusic_db_sqlite.sql'));
	} catch (PDOException $e) {
		echo "\nOh. Shit. Something's wrong: Couldn't write to the database. $e\n\n";
		die();
		break;
	}

	$user_email    = 'root@localhost';
	$system_salt   = md5($user_email . time());
	$user_password = "hack_my_gibson";
	$password_hash = hash_hmac('sha256', $user_password, $system_salt);

	$data = array(
		'email_address' => $user_email,
		'password'      => $password_hash,
		'is_admin'      => true,
		'api_key'       => $api_key = hash_hmac('md5', time() . $password_hash . rand(976654,1234567267), $system_salt) . substr((string) time(),6),
		'api_secret'    => hash_hmac('sha256', time() . $password_hash . rand(976654,1234567267), $system_salt),
		'creation_date' => time()
	);
	$query = "INSERT INTO user_users (email_address,password,is_admin,api_key,api_secret,creation_date) VALUES (:email_address,:password,:is_admin,:api_key,:api_secret,:creation_date)";
	
	try {
		$q = $pdo->prepare($query);
	} catch (PDOException $e) {
		echo "\nOh. Shit. Something's wrong: Couldn't prepare query. $e\n\n";
		die();
		break;
	}
	
	try {
		$success = $q->execute($data);
	} catch(PDOException $e) {
		echo "\nOh. Shit. Something's wrong. Couldn't add the user to the database. $e\n\n";
		die();
		break;
	}

	if ($success) {
		$installer_root = dirname(__FILE__);
		// modify settings files
		if (
			!copy($installer_root.'/../../framework/php/settings/cashmusic_template.ini.php',$installer_root.'/../../framework/php/settings/cashmusic.ini.php')
		) {
			echo '\nOh. Shit. Something\'s wrong. Couldn\'t write the config file.\n\n'
			. 'the directory you specified for the framework.</p>';
			break;
		}

		// move source files into place
		$file_write_success = false;
		if (
			findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','driver = "mysql','driver = "sqlite') &&
			findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','debug = 0','debug = 1') &&
			findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','database = "seed','database = "cashmusic_test.sqlite') &&
			findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','salt = "I was born of sun beams; Warming up our limbs','salt = "' . $system_salt)
		) {
			$file_write_success = true;
		} 
		if (!$file_write_success) {
			echo "\nOh. Shit. Something's wrong. We had trouble editing a few files. Please try again.\n\n";
			break;
		} else {
			try {
				$pdo->exec(file_get_contents(dirname(__FILE__) . '/../../framework/php/settings/sql/cashmusic_demo_data.sql'));
				echo "\nTEST DATABASE DEPLOYED! Fear of testing is the mind-killer.\n";
			} catch (PDOException $e) { 
				echo "\nSOME SUCCESS, SOME FAILURE:\nEverything is set up properly, but there was an error writing demo data.\n$e\n";
			}
		}
	}
}
?>
