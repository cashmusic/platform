<?php
/**
 * CASH Music Dev Installer
 *
 * Takes the CASH DIY repo and sets it up  as a working platform instance that
 * can be directly coded against. Command-line script.
 *
 * USAGE:
 * php installers/php/dev_installer.php 
 * follow prompts. 
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
function readStdin($prompt, $valid_inputs = false, $default = '') {
	// Courtesy of http://us3.php.net/manual/en/features.commandline.io-streams.php#101307
	while(!isset($input) || (is_array($valid_inputs) && !in_array(strtolower($input), $valid_inputs))) {
		echo $prompt;
		$input = strtolower(trim(fgets(STDIN)));
		if(empty($input) && !empty($default)) {
			$input = $default;
		}
	}
	return $input;
}

if(!defined('STDIN')) { // force CLI, the browser is *so* 2007...
	echo "Please run installer from the command line. usage:<br / >&gt; php installers/php/dev_installer.php";
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

echo "\n                       :+#\n"
	. "              #   +###+###`\n"
	. "              ############\n"
	. "               ###########:\n"
	. "               ,###########`\n"
	. "                ############\n"
	. "                #############;\n"
	. "                .################\n"
	. "                 :#################\n"
	. "                 ;##################\n"
	. "                  .##################\n"
	. "                   ;#################\n"
	. "                    #################;\n"
	. "                     ############ ###;\n"
	. "                     ############;###\n"
	. "                     ###,####+,###'##\n"
	. "                     ###    ,# ### ##\n"
	. "                     ###     # +## #+\n"
	. "                     ,##     # .## ;+\n"
	. "                      ##     #+ ## :#\n"
	. "                      ##     ## ## '#\n"
	. "                      ##     .  ## ##\n"
	. "                      ##        ## ##\n"
	. "                      ``            \n\n"
	. "                     C A S H  M U S I C\n"
	. "                   PLATFORM DEV INSTALLER";
	echo "\n\nYou are in an open field west of a big white house with a boarded front door.\n\n";
	// you can input <Enter> or 1, 2, 3 
	$db_engine = readStdin('What database engine do you want to use? (\'mysql\'|\'sqlite\'): ', array('mysql', 'sqlite'));
	$installer_root = dirname(__FILE__);

	if ($db_engine == 'mysql') {
		if (@new PDO()) {
			echo "\nOh. Shit. Something's wrong: PDO is required.\n\n";
			die();
		}
		
		$db_server      = readStdin('Database server (default: \'127.0.0.1:3306\'): ', false,'127.0.0.1:3306');
		$db_name        = readStdin('Database name: ');
		$db_username    = readStdin('Database username: ');
		$db_password    = readStdin('Database password: ');
		
		// set up database, add user / password
		$user_password = substr(md5($system_salt . 'password'),4,7);
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
		} catch (PDOException $e) {
			echo "\nOh. Shit. Something's wrong: Couldn't connect to the database. $e\n\n";
			die();
			break;
		}

		if ($pdo->query(file_get_contents(dirname(__FILE__) . '/../../framework/php/settings/sql/cashmusic_db.sql'))) {
			$success = true;
		} else {
			echo "\nOh. Shit. Something's wrong. Couldn't create database tables.\n\n";
			die();
			break;
		}
	} else if ($db_engine == 'sqlite') {
		// if the file exists already, rename it as a backup
		if (file_exists($installer_root . '/../../framework/db/cashmusic.sqlite')) {
			rename($installer_root . '/../../framework/db/cashmusic.sqlite',$installer_root . '/../../framework/db/cashmusic.sqlite.bak');
		} else {
			// if the directory was never created then create it now
			if (!file_exists($installer_root . '/../../framework/db')) {
				mkdir($installer_root . '/../../framework/db');
			}
		}
		chmod($installer_root . '/../../framework/db',0755);
		
		// connect to the new db...will create if not found
		try {
			$pdo = new PDO ('sqlite:' . $installer_root . '/../../framework/db/cashmusic.sqlite');
			$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			chmod($installer_root . '/../../framework/db/cashmusic.sqlite',0755);
		} catch (PDOException $e) {
			echo "\nOh. Shit. Something's wrong: Couldn't connect to the database. $e\n\n";
			die();
			break;
		}

		// push in all the tables
		try {
			$pdo->exec(file_get_contents($installer_root . '/../../framework/php/settings/sql/cashmusic_db_sqlite.sql'));
			$success = true;
		} catch (PDOException $e) {
			echo "\nOh. Shit. Something's wrong: Couldn't write to the database. $e\n\n";
			die();
			break;
		}
	}

	if ($success) {
		$user_email     = readStdin("\nLogin email address: ");
		$user_password  = readStdin("Login password: ");
		$default_salt   = readStdin("\nUse defaut system salt? (y/n): ", false, 'y');
		$force_hmac     = readStdin("\nForce HMAC hash? (test 5.2 without crypt) (y/n): ", false, 'n');
		
		if (strtolower(substr($default_salt,0,1)) == 'y') {
			$system_salt = 'I was born of sun beams; Warming up our limbs';
		} else {
			$system_salt = md5($user_email . time());
		}

		if (!defined('CRYPT_BLOWFISH')) define('CRYPT_BLOWFISH', 0);
		if (!defined('CRYPT_SHA512')) define('CRYPT_SHA512', 0);
		if (!defined('CRYPT_SHA256')) define('CRYPT_SHA256', 0);

		if (CRYPT_BLOWFISH + CRYPT_SHA512 + CRYPT_SHA256 && strtolower(substr($force_hmac,0,1)) != 'y') {
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
			'api_key'       => $api_key = hash_hmac('md5', time() . $password_hash . rand(976654,1234567267), $system_salt) . substr((string) time(),6),
			'api_secret'    => hash_hmac('sha256', time() . $password_hash . rand(976654,1234567267), $system_salt),
			'creation_date' => time()
		);
		$query = "INSERT INTO people (email_address,password,is_admin,api_key,api_secret,creation_date) VALUES (:email_address,:password,:is_admin,:api_key,:api_secret,:creation_date)";
		
		try {  
			$q = $pdo->prepare($query);
			$success = $q->execute($data);
		} catch(PDOException $e) {  
			echo "\nOh. Shit. Something's wrong. Couldn't add the user to the database. $e\n\n";
			die();
			break;
		}

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
		if ($db_engine == "sqlite") {
			if (
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','driver = "mysql','driver = "sqlite') &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','database = "cashmusic','database = "cashmusic.sqlite') &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','salt = "I was born of sun beams; Warming up our limbs','salt = "' . $system_salt)
			) {
				$file_write_success = true;
			} 
		} else {
			if (
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','hostname = "127.0.0.1:8889','hostname = "' . $db_server) &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','username = "root','username = "' . $db_username) &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','password = "root','password = "' . $db_password) &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','database = "cashmusic','database = "' . $db_name) &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','salt = "I was born of sun beams; Warming up our limbs','salt = "' . $system_salt)
			) {
				$file_write_success = true;
			}
		}
		if (!$file_write_success) {
			echo "\nOh. Shit. Something's wrong. We had trouble editing a few files. Please try again.\n\n";
			break;
		} else {
			echo "\nSUCCESS!\n\nLogin using:\n\nemail: $user_email\npassword: $user_password\n\n";
		}
	}
}
?>
