<?php
/**
 * Upgrade script: v4 to v5
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */

// $current_version = 2;
$upgrade_failure = false;

if ($current_version == 4) {

	/***************************
	 *
	 * 0. DEFINITIONS
	 *
	 ***************************/
	$cash_settings = parse_ini_file(CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php');
	$cash_settings['port'] = '';
	if (strpos($cash_settings['hostname'],':') === false) {
		$cash_settings['port'] = 3306;
	} else {
		if (!substr($hostname,0,2) == ':/') {
			$host_and_port = explode(':',$hostname);
			$cash_settings['hostname'] = $host_and_port[0];
			$cash_settings['port'] = $host_and_port[1];
		}
	}

	/***************************
	 *
	 * 1. DO SCHEMA CHANGES
	 *
	 ***************************/

	try {  
		if ($cash_settings['driver'] == 'sqlite') {
			@copy(CASH_PLATFORM_ROOT . "/../db/{$cash_settings['database']}",CASH_PLATFORM_ROOT . "/../db/{$cash_settings['database']}.bak");
      		chmod(CASH_PLATFORM_ROOT . "/../db/{$cash_settings['database']}.bak",0755);
			$pdo = new PDO("sqlite:" . CASH_PLATFORM_ROOT . "/../db/{$cash_settings['database']}");
		} else {
			if (substr($cash_settings['hostname'],0,2) == ':/') {
				$pdo = new PDO("{$cash_settings['driver']}:unix_socket={$cash_settings['hostname']};dbname={$cash_settings['database']}", $cash_settings['username'], $cash_settings['password']);
			} else {
				$pdo = new PDO("{$cash_settings['driver']}:host={$cash_settings['hostname']};port={$cash_settings['port']};dbname={$cash_settings['database']}", $cash_settings['username'], $cash_settings['password']);
			}
		}
		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	} catch(PDOException $e) {  
		$upgrade_failure = true;
	}

	if (!$upgrade_failure) {
		if ($cash_settings['driver'] == 'sqlite') {
			$query = <<<SQLITE
BEGIN TRANSACTION;

CREATE TABLE system_sessions (
  id INTEGER PRIMARY KEY,
  session_id text,
  data text,
  client_ip text,
  client_proxy text,
  expiration_date integer,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);
CREATE INDEX system_sessions_session_id ON system_sessions (session_id);
CREATE INDEX system_sessions_expiration_date ON system_sessions (expiration_date);

COMMIT;
SQLITE;
		} else {
			$query = <<<MYSQL
ALTER TABLE `people` 
  CHANGE `password` `password` varchar(255) NOT NULL DEFAULT '';

DROP TABLE IF EXISTS `system_sessions`;
CREATE TABLE `system_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `client_ip` varchar(39),
  `client_proxy` varchar(39),
  `expiration_date` int(11),
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_sessions_session_id` (`session_id`),
  KEY `system_sessions_expiration_date` (`expiration_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
MYSQL;
		}
	}

	try {
		$pdo->exec($query);
	} catch (PDOException $e) {
		if ($cash_settings['driver'] == 'sqlite') {
			unlink(CASH_PLATFORM_ROOT . "/../db/{$cash_settings['database']}");
			@rename(CASH_PLATFORM_ROOT . "/../db/{$cash_settings['database']}.bak",CASH_PLATFORM_ROOT . "/../db/{$cash_settings['database']}");
	    }
		$upgrade_failure = true;
	}

	/***************************
	 *
	 * 1. MODIFY NEW FILES
	 *
	 ***************************/

	$api_dir = rtrim(dirname($_SERVER['REQUEST_URI']),'/') . '/api';
	$public_dir = rtrim(dirname($_SERVER['REQUEST_URI']),'/') . '/public';

	if (
		!findReplaceInFile('./update/interfaces/php/api/.htaccess','RewriteBase /interfaces/php/api','RewriteBase ' . $api_dir) || 
		!findReplaceInFile('./update/interfaces/php/public/request/.htaccess','RewriteBase /interfaces/php/public/request','RewriteBase ' . $public_dir . '/request') || 
		
		!findReplaceInFile('./update/interfaces/php/public/request/constants.php','$cashmusic_root = dirname(__FILE__) . "/../../../../framework/php/cashmusic.php','$cashmusic_root = "' . $cash_root_location . '/cashmusic.php') || 
		!findReplaceInFile('./update/interfaces/php/api/controller.php',"define('CASH_PLATFORM_ROOT', dirname(__FILE__).'/../../../framework/php","define('CASH_PLATFORM_ROOT', '" . $cash_root_location)
	) {
		$upgrade_failure = true;
	}
}
?>