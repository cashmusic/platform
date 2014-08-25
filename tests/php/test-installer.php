<?php
/**
 * CASH Music Test Installer
 *
 * Takes the CASH DIY repo and sets it up  as a working platform instance so
 * tests can be run against it.
 *
 * USAGE:
 * php tests/php/test-installer.php
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
	echo "Please run installer from the command line. usage:<br / >&gt; php tests/php/test-installer.php";
} else {
	require_once(dirname(__FILE__) .'/../../tests/php/functions.php');
	
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

echo "\n\n                                  /)-_-(/\n"
	. "                                   (o o)\n"
	. "                           .-----__/\o/\n"
	. "                          /  __      /\n"
	. "                      \__/\ /  \_\ |/\n"
	. "                           \/\     ||\n"
	. "                       o   //     ||\n"
	. "                      xxx  |\     |\ \n"
	. "\n\n"
	. "                     C A S H  M U S I C\n"
	. "                   PLATFORM TEST INSTALLER\n";
	echo "\nPh'nglui mglw'nafh Cthulhu R'lyeh wgah'nagl fhtagn!\n\n";
	$db_engine = 'sqlite';
	$installer_root = dirname(__FILE__);

	if (getTestEnv("CASHMUSIC_DB_TYPE")) {
		$db_engine = getTestEnv("CASHMUSIC_DB_TYPE");
		if ($db_engine != 'mysql' && $db_engine != 'sqlite') {
			echo "\nOh. Shit. Something's wrong: first argument must be either 'sqlite' or 'mysql' \n\n";
			exit(1);
		}
		if ($db_engine == 'mysql') {
			$db_server    = getTestEnv("CASHMUSIC_DB_SERVER");
			$db_name      = getTestEnv("CASHMUSIC_DB_NAME");
			$db_username  = getTestEnv("CASHMUSIC_DB_USERNAME");
			$db_password  = getTestEnv("CASHMUSIC_DB_PASSWORD");
		}
	}

	if ($db_engine == 'mysql') {
		if (@new PDO()) {
			echo "\nOh. Shit. Something's wrong: PDO is required.\n\n";
			exit(1);
		}
		
		// set up database, add user / password
		$db_port = 3306;
		if (strpos($db_server,':') !== false) {
			$host_and_port = explode(':',$db_server);
			$db_address = $host_and_port[0];
			$db_port = $host_and_port[1];
		} else {
			$db_address = $db_server;
		}
		try {
			$pdo = new PDO ("mysql:host=$db_address;port=$db_port;dbname=$db_name",$db_username,$db_password);
			$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		} catch (PDOException $e) {
			echo "\nOh. Shit. Something's wrong: Couldn't connect to the database. $e\n\n";
			exit(1);
			break;
		}

		$pdo_result = $pdo->query(file_get_contents(dirname(__FILE__) . '/../../framework/settings/sql/cashmusic_db.sql'));
		if ($pdo_result) {
			$pdo_errors = $pdo_result->errorInfo();
			$pdo_result->closeCursor();
			echo "DB CREATION ERROR/WARNING INFO:\n" . print_r($pdo_errors,true);
			$success = true;
		} else {
			echo "\nOh. Shit. Something's wrong. Couldn't create database tables.\n\n";
			exit(1);
			break;
		}
	} else if ($db_engine == 'sqlite') {
		// if the file exists already, rename it as a backup
		if (file_exists($installer_root . '/../../framework/db/cashmusic_test.sqlite')) {
			rename($installer_root . '/../../framework/db/cashmusic_test.sqlite',$installer_root . '/../../framework/db/cashmusic_test.sqlite.pretest.bak');
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
			chmod($installer_root . '/../../framework/db/cashmusic_test.sqlite',0755);
		} catch (PDOException $e) {
			echo "\nOh. Shit. Something's wrong: Couldn't connect to the database. $e\n\n";
			exit(1);
			break;
		}

		// push in all the tables
		// TO-DO:
		// what the fuck?
		// 
		// $pdo->query doesn't work here. fails and doesn't write to the DB. exec works like a charm
		// but for the MySQL version the behavior is reversed. should both (seemingly) work interchangeably
		// 
		// i don't fucking know.
		try {
			$pdo->exec(file_get_contents($installer_root . '/../../framework/settings/sql/cashmusic_db_sqlite.sql'));
			$success = true;
		} catch (PDOException $e) {
			echo "\nOh. Shit. Something's wrong: Couldn't write to the database. $e\n\n";
			exit(1);
			break;
		}
	}

	if ($success) {
		$user_email    = 'root@localhost';
		$system_salt   = md5($user_email . time());
		$user_password = 'hack_my_gibson';

		if (!defined('CRYPT_BLOWFISH')) define('CRYPT_BLOWFISH', 0);
		if (!defined('CRYPT_SHA512')) define('CRYPT_SHA512', 0);
		if (!defined('CRYPT_SHA256')) define('CRYPT_SHA256', 0);

		if (CRYPT_BLOWFISH + CRYPT_SHA512 + CRYPT_SHA256) {
			if (CRYPT_BLOWFISH == 1) {
				$password_hash = crypt(md5($user_password . $system_salt), '$2a$13$' . md5(time() . $system_salt) . '$');
			} else if (CRYPT_SHA512 == 1) {
				$password_hash = crypt(md5($user_password . $system_salt), '$6$rounds=6666$' . md5(time() . $system_salt) . '$');
			} else if (CRYPT_SHA256 == 1) {
				$password_hash = crypt(md5($user_password . $system_salt), '$5$rounds=6666$' . md5(time() . $system_salt) . '$');
			}
		} else {
			$key = time();
			$password_hash = $key . '$' . hash_hmac('sha256', md5($user_password . $system_salt), $key);
		}
	
		$data = array(
			'email_address' => $user_email,
			'password'      => $password_hash,
			'is_admin'      => true,
			'api_key'       => '42',
			'api_secret'    => '43',
			'creation_date' => time()
		);
		$query = "INSERT INTO people (email_address,password,is_admin,api_key,api_secret,creation_date) VALUES (:email_address,:password,:is_admin,:api_key,:api_secret,:creation_date)";
		
		try {  
			$q = $pdo->prepare($query);
			$success = $q->execute($data);
		} catch(PDOException $e) {  
			echo "\nOh. Shit. Something's wrong. Couldn't add the user to the database. $e\n\n";
			exit(1);
			break;
		}

		// modify settings files
		if (file_exists($installer_root . '/../../framework/settings/cashmusic.ini.php')) {
			rename($installer_root . '/../../framework/settings/cashmusic.ini.php',$installer_root . '/../../framework/settings/cashmusic.ini.pretest.bak');
		}
		if (
			!copy($installer_root.'/../../framework/settings/cashmusic_template.ini.php',$installer_root.'/../../framework/settings/cashmusic.ini.php')
		) {
			echo '\nOh. Shit. Something\'s wrong. Couldn\'t write the config file.\n\n'
			. 'the directory you specified for the framework.</p>';
			break;
		}

		// move source files into place
		$file_write_success = false;
		$test_url = getTestEnv("CASHMUSIC_TEST_URL");
		if (!$test_url) { $test_url = "http://dev.cashmusic.org"; }
		if ($db_engine == 'sqlite') {
			if (
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','driver = "mysql','driver = "sqlite') &&
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','debug = no','debug = yes') &&
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','database = "cashmusic','database = "cashmusic_test.sqlite') &&
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','apilocation = "http://localhost/api/','apilocation = "'.$test_url.'/api/') &&
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','salt = "I was born of sun beams; Warming up our limbs','salt = "' . $system_salt)
			) {
				$file_write_success = true;
			} 
		} else {
			if (
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','hostname = "127.0.0.1:8889','hostname = "' . $db_server) &&
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','username = "root','username = "' . $db_username) &&
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','password = "root','password = "' . $db_password) &&
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','database = "cashmusic','database = "' . $db_name) &&
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','debug = no','debug = yes') &&
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','apilocation = "http://localhost/api/','apilocation = "'.$test_url.'/api/') &&
				findReplaceInFile($installer_root.'/../../framework/settings/cashmusic.ini.php','salt = "I was born of sun beams; Warming up our limbs','salt = "' . $system_salt)
			) {
				$file_write_success = true;
			}
		}
		if (!$file_write_success) {
			echo "\nOh. Shit. Something's wrong. We had trouble editing a few files. Please try again.\n\n";
			break;
		} else {
			echo "\n" . strtoupper($db_engine) . " TEST DB DEPLOYED! Fear of testing is the mind-killer.\n";
		}
	}
}
?>
