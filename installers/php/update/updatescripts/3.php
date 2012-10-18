<?php
/**
 * Upgrade script: v3 to v4
 *
 * @package diy.org.cashmusic
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

if ($current_version == 3) {

	include_once(dirname(__FILE__) . '/../../../../../admin/constants.php');

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

CREATE TABLE assets_temp (
  id INTEGER PRIMARY KEY,
  user_id integer DEFAULT NULL,
  parent_id integer DEFAULT NULL,
  location text,
  public_url text,
  connection_id integer DEFAULT NULL,
  type text DEFAULT 'file',
  title text,
  description text,
  metadata text,
  public_status integer DEFAULT '0',
  size integer DEFAULT '0',
  hash text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);

INSERT INTO assets_temp (id,user_id,parent_id,location,public_url,connection_id,type,title,description,public_status,size,hash,creation_date,modification_date)
  SELECT id,user_id,parent_id,location,public_url,connection_id,type,title,description,public_status,size,hash,creation_date,modification_date
  FROM assets;

DROP TABLE assets;
CREATE TABLE assets (
  id INTEGER PRIMARY KEY,
  user_id integer DEFAULT NULL,
  parent_id integer DEFAULT NULL,
  location text,
  public_url text,
  connection_id integer DEFAULT NULL,
  type text DEFAULT 'file',
  title text,
  description text,
  metadata text,
  public_status integer DEFAULT '0',
  size integer DEFAULT '0',
  hash text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);
CREATE INDEX asst_asets_parent_id ON assets (parent_id);
CREATE INDEX assets_user_id ON assets (user_id);
INSERT INTO assets (id,user_id,parent_id,location,public_url,connection_id,type,title,description,public_status,size,hash,creation_date,modification_date)
  SELECT id,user_id,parent_id,location,public_url,connection_id,type,title,description,public_status,size,hash,creation_date,modification_date
  FROM assets_temp;
DROP TABLE assets_temp;

UPDATE assets 
  SET type = 'file'
  WHERE type = 'storage';


CREATE TABLE commerce_transactions_temp (
  id integer PRIMARY KEY,
  user_id integer,
  connection_id integer,
  connection_type text,
  service_timestamp text,
  service_transaction_id text DEFAULT '',
  data_sent text,
  data_returned text,
  successful integer DEFAULT '0',
  gross_price numeric,
  service_fee numeric,
  status text DEFAULT 'abandoned',
  creation_date integer DEFAULT '0',
  modification_date integer DEFAULT '0'
);

INSERT INTO commerce_transactions_temp (id,user_id,connection_id,connection_type,service_timestamp,service_transaction_id,data_sent,data_returned,successful,gross_price,service_fee,status,creation_date,modification_date)
  SELECT id,user_id,connection_id,connection_type,service_timestamp,service_transaction_id,data_sent,data_returned,successful,gross_price,service_fee,status,creation_date,modification_date
  FROM commerce_transactions;

DROP TABLE commerce_transactions;
CREATE TABLE commerce_transactions (
  id integer PRIMARY KEY,
  user_id integer,
  connection_id integer,
  connection_type text,
  service_timestamp text,
  service_transaction_id text DEFAULT '',
  data_sent text,
  data_returned text,
  successful integer DEFAULT '0',
  gross_price numeric,
  service_fee numeric,
  status text DEFAULT 'abandoned',
  creation_date integer DEFAULT '0',
  modification_date integer DEFAULT '0'
);

INSERT INTO commerce_transactions (id,user_id,connection_id,connection_type,service_timestamp,service_transaction_id,data_sent,data_returned,successful,gross_price,service_fee,status,creation_date,modification_date)
  SELECT id,user_id,connection_id,connection_type,service_timestamp,service_transaction_id,data_sent,data_returned,successful,gross_price,service_fee,status,creation_date,modification_date
  FROM commerce_transactions_temp;
DROP TABLE commerce_transactions_temp;


CREATE TABLE people_contacts_temp (
  id INTEGER PRIMARY KEY,
  user_id integer DEFAULT '0',
  email_address text,
  first_name text,
  last_name text,
  organization text,
  address_line1 text,
  address_line2 text,
  address_city text,
  address_region text,
  address_postalcode text,
  address_country text,
  phone text,
  notes text,
  links text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

INSERT INTO people_contacts_temp (id,user_id,email_address,first_name,last_name,organization,address_line1,address_line2,address_city,address_region,address_postalcode,address_country,notes,creation_date,modification_date)
  SELECT id,user_id,email_address,first_name,last_name,organization,address_line1,address_line2,address_city,address_region,address_postalcode,address_country,notes,creation_date,modification_date
  FROM people_contacts;

DROP TABLE people_contacts;
CREATE TABLE people_contacts (
  id INTEGER PRIMARY KEY,
  user_id integer DEFAULT '0',
  email_address text,
  first_name text,
  last_name text,
  organization text,
  address_line1 text,
  address_line2 text,
  address_city text,
  address_region text,
  address_postalcode text,
  address_country text,
  phone text,
  notes text,
  links text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

INSERT INTO people_contacts (id,user_id,email_address,first_name,last_name,organization,address_line1,address_line2,address_city,address_region,address_postalcode,address_country,notes,creation_date,modification_date)
  SELECT id,user_id,email_address,first_name,last_name,organization,address_line1,address_line2,address_city,address_region,address_postalcode,address_country,notes,creation_date,modification_date
  FROM people_contacts_temp;
DROP TABLE people_contacts_temp;


CREATE TABLE people_resetpassword_temp (
  id INTEGER PRIMARY KEY,
  key text,
  user_id integer DEFAULT '0',
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

INSERT INTO people_resetpassword_temp (id,key,user_id,creation_date,modification_date)
  SELECT id,random_key,user_id,creation_date,modification_date
  FROM people_resetpassword;

DROP TABLE people_resetpassword;
CREATE TABLE people_resetpassword (
  id INTEGER PRIMARY KEY,
  key text,
  user_id integer DEFAULT '0',
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

INSERT INTO people_resetpassword (id,key,user_id,creation_date,modification_date)
  SELECT id,key,user_id,creation_date,modification_date
  FROM people_resetpassword_temp;
DROP TABLE people_resetpassword_temp;

COMMIT;
SQLITE;
		} else {
			$query = <<<MYSQL
ALTER TABLE `assets` 
  ADD COLUMN `metadata` text,
  CHANGE `type` `type` varchar(255) DEFAULT 'file';

UPDATE `assets` 
  SET type = 'file'
  WHERE type = 'storage';

ALTER TABLE `commerce_transactions` 
  CHANGE `service_timestamp` `service_timestamp` varchar(255) NOT NULL;

ALTER TABLE `people_contacts` 
  ADD COLUMN `phone` varchar(255),
  ADD COLUMN `links` text;

ALTER TABLE `people_resetpassword` 
  DROP COLUMN `time_requested`,
  CHANGE `random_key` `key` varchar(255) NOT NULL;
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