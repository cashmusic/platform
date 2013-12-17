<?php
/**
 * CASH Music Vagrant Installer
 *
 * Automates the initial platform setup for "vagrant up" platform instances.
 * 
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
*/

echo "\n\n                                  /)-_-(/\n"
	. "                                   (o o)\n"
	. "                           .-----__/\o/\n"
	. "                          /  __      /\n"
	. "                      \__/\ /  \_\ |/\n"
	. "                           \/\     ||\n"
	. "                       o   //     ||\n"
	. "                      xxx  |\     |\ \n"
	. "\n\n"
	. "                     C A S H  M U S I C\n\n";
	$installer_root = dirname(__FILE__);

if (!file_exists('/vagrant/framework/db/cashmusic_vagrant.sqlite')) {
	// if the directory was never created then create it now
	if (!file_exists('/vagrant/framework/db')) {
		mkdir('/vagrant/framework/db');
	}
	
	// connect to the new db...will create if not found
	try {
		$pdo = new PDO ('sqlite:/vagrant/framework/db/cashmusic_vagrant.sqlite');
		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		chmod('/vagrant/framework/db/cashmusic_vagrant.sqlite',0755);
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
		$pdo->exec(file_get_contents('/vagrant/framework/php/settings/sql/cashmusic_db_sqlite.sql'));
		$success = true;
	} catch (PDOException $e) {
		echo "\nOh. Shit. Something's wrong: Couldn't write to the database. $e\n\n";
		exit(1);
		break;
	}

	if ($success) {
		$user_email    = 'dev@cashmusic.org';
		$system_salt   = 'this is a very bad salt to choose';
		$user_password = 'dev';

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
			'api_key'       => '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a',
			'api_secret'    => '1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b1b',
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
	}
}

?>
