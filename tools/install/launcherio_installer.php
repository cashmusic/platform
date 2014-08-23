<?php
/**
 * CASH Music launcher.io Installer
 *
 * Takes the CASH platform repo and sets it up  as a working platform instance that
 * can be directly coded against. Command-line script.
 *
 * USAGE:
 * php installers/php/dev_installer.php 
 * follow prompts. 
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

// only run if the cashmusic_platform_settings is not valid JSON — security measure
// to ensure the script isn't run after setup is complete. the admin *should* delete
// this file, but you know shit gets cray...
if (!json_decode(getenv('cashmusic_platform_settings'),true)) {
	$success = false;
	$installer_root = dirname(__FILE__);

	$services = getenv("VCAP_SERVICES");
	$services_json = json_decode($services,true);
	$mysql_config = $services_json["mysql-5.1"][0]["credentials"];

	$db_address = $mysql_config["hostname"];
	$db_port =  $mysql_config["port"];
	$db_name = $mysql_config["name"];
	$db_username = $mysql_config["user"];
	$db_password = $mysql_config["password"];

	$user_email = $_REQUEST["user_email"];
	$user_password = $db_password;
	$host = $_REQUEST["host"];
	$name = $_REQUEST["name"];

	// set up database, add user / password
	try {
		$pdo = new PDO ("mysql:host=$db_address;port=$db_port;dbname=$db_name",$db_username,$db_password);
	} catch (PDOException $e) {
		echo 'Nope. PDO asploded.';
		die();
		break;
	}

	if ($pdo->query(file_get_contents(dirname(__FILE__) . '/framework/settings/sql/cashmusic_db.sql'))) {
		$success = true;
	} else {
		echo 'Error setting up the database.';
		die();
		break;
	}

	if ($success) {	
		$system_salt = md5($user_email . time());

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
			'api_key'       => hash_hmac('md5', time() . $password_hash . rand(976654,1234567267), $system_salt) . substr((string) time(),6),
			'api_secret'    => hash_hmac('sha256', time() . $password_hash . rand(976654,1234567267), $system_salt),
			'creation_date' => time()
		);
		$query = "INSERT INTO people (email_address,password,is_admin,api_key,api_secret,creation_date) VALUES (:email_address,:password,:is_admin,:api_key,:api_secret,:creation_date)";
		
		try {  
			$q = $pdo->prepare($query);
			$success = $q->execute($data);
		} catch(PDOException $e) {  
			echo 'Could not add user.';
			die();
			break;
		}

		// TODO: verify that $host is a fully-httpified url, if not, httpize that shit

		$cashmusic_env_var = json_encode(
			array(
				"driver" => "mysql",
				"hostname" => $db_address . ':' . $db_port,
				"username" => $db_username,
				"password" => $db_password,
				"database" => $db_name,
				"salt" => $system_salt,
				"debug" => "",
				"apilocation" => $host . "/api/",
				"instancetype" => "single",
				"timezone" => "US/Pacific",
				"systememail" => $user_email,
				"smtp" => "0",
				"smtpserver" => "",
				"smtpport" => "",
				"smtpusername" => "",
				"smtppassword" => "",
				"platforminitlocation" => '/framework/cashmusic.php'
			)
		);
		// Need an extra escape on double-quotes for AF env variables
		$cashmusic_env_var = str_replace('"','\"',$cashmusic_env_var);
		
		// TODO: research cloud foundry API...can we set a permanent environment variable for the service?
		//       (guessing it's a "no" but still worth checking.)

		// TODO: can we set the cashmusic_platform_settings variable in the manifest? even if it's a blank
		//       string it'd be one less point of failure by having the var name correct

		echo "Success. Add an environment varable named 'cashmusic_platform_settings' containing:\n\n$cashmusic_env_var";
		mail($user_email,"CASH Music setup","\nINSTALLER SUCCESS!\n\nTo complete installation you'll need to set an environment variable called 'cashmusic_platform_settings' on the target server. Log in to your service provider to do so. Set the variable to:\n\n$cashmusic_env_var\n\n\nTo log in after adding the environment variable, visit $host/admin/ and use this email address and your service account password.\n\n");
	}
}
?>