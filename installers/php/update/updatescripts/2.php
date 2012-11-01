<?php
/**
 * Upgrade script: v2 to v3
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

if ($current_version == 2) {

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

CREATE TABLE system_lock_codes_temp (
  id INTEGER PRIMARY KEY,
  uid text,
  scope_table_alias text DEFAULT 'elements',
  scope_table_id integer,
  user_id integer,
  claim_date integer DEFAULT NULL,
  creation_date integer DEFAULT '0',
  modification_date integer DEFAULT NULL
);

INSERT INTO system_lock_codes_temp (id,uid,scope_table_id,claim_date,creation_date,modification_date)
  SELECT id,uid,element_id,claim_date,creation_date,modification_date
  FROM system_lock_codes;

DROP TABLE system_lock_codes;
CREATE TABLE system_lock_codes (
  id INTEGER PRIMARY KEY,
  uid text,
  scope_table_alias text DEFAULT 'elements',
  scope_table_id integer,
  user_id integer,
  claim_date integer DEFAULT NULL,
  creation_date integer DEFAULT '0',
  modification_date integer DEFAULT NULL
);
CREATE INDEX system_lock_codes_uid ON system_lock_codes (uid);
CREATE INDEX system_lock_codes_user_id ON system_lock_codes (user_id);

INSERT INTO system_lock_codes (id,uid,scope_table_id,claim_date,creation_date,modification_date)
  SELECT id,uid,scope_table_id,claim_date,creation_date,modification_date
  FROM system_lock_codes_temp;
DROP TABLE system_lock_codes_temp;

COMMIT;
SQLITE;
		} else {
			$query = <<<MYSQL
ALTER TABLE `system_lock_codes` 
  ADD COLUMN `scope_table_alias` varchar(255) DEFAULT 'elements',
  ADD COLUMN `user_id` int(11) DEFAULT NULL,
  DROP INDEX `system_lock_codes_element_id`,
  CHANGE `element_id` `scope_table_id` int(11) DEFAULT NULL,
  ADD INDEX `system_lock_codes_uid` (`uid`),
  ADD INDEX `system_lock_codes_user_id` (`user_id`);
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
}
?>