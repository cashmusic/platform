<?php
/**
 * Upgrade script: v1 to v2
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

// $current_version = 1;
$upgrade_failure = false;

if ($current_version == 1) {

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

  function simpleXOR($input, $key) {
  	// append key on itself until it is longer than the input
  	while (strlen($key) < strlen($input)) { $key .= $key; }

  	// trim key to the length of the input
  	$key = substr($key, 0, strlen($input));

  	// Simple XOR'ing, each input byte with each key byte.
  	$result = '';
  	for ($i = 0; $i < strlen($input); $i++) {
  		$result .= $input{$i} ^ $key{$i};
  	}
  	return $result;
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

CREATE TEMPORARY TABLE assets_temp (
  id INTEGER PRIMARY KEY,
  user_id integer DEFAULT NULL,
  parent_id integer DEFAULT NULL,
  location text,
  public_url text,
  connection_id integer DEFAULT NULL,
  type text DEFAULT 'storage',
  title text,
  description text,
  public_status integer DEFAULT '0',
  size integer DEFAULT '0',
  hash text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);

INSERT INTO assets_temp (id,user_id,parent_id,location,connection_id,title,description,public_status,creation_date,modification_date)
  SELECT id,user_id,parent_id,location,connection_id,title,description,public_status,creation_date,modification_date
  FROM assets;

DROP TABLE assets;
CREATE TABLE assets (
  id INTEGER PRIMARY KEY,
  user_id integer DEFAULT NULL,
  parent_id integer DEFAULT NULL,
  location text,
  public_url text,
  connection_id integer DEFAULT NULL,
  type text DEFAULT 'storage',
  title text,
  description text,
  public_status integer DEFAULT '0',
  size integer DEFAULT '0',
  hash text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);
CREATE INDEX asst_asets_parent_id ON assets (parent_id);
CREATE INDEX assets_user_id ON assets (user_id);

INSERT INTO assets (id,user_id,parent_id,location,connection_id,title,description,public_status,creation_date,modification_date)
  SELECT id,user_id,parent_id,location,connection_id,title,description, public_status, creation_date, modification_date
  FROM assets_temp;
DROP TABLE assets_temp;

DROP TABLE commerce_assets;

DROP TABLE commerce_items;
CREATE TABLE commerce_items (
  id integer PRIMARY KEY,
  user_id integer,
  name text DEFAULT NULL,
  description text,
  sku text DEFAULT NULL,
  price numeric DEFAULT NULL,
  flexible_price numeric DEFAULT NULL,
  digital_fulfillment integer DEFAULT '0',
  physical_fulfillment integer DEFAULT '0',
  physical_weight integer,
  physical_width integer,
  physical_height integer,
  physical_depth integer,
  available_units integer DEFAULT '0',
  variable_pricing integer DEFAULT '0',
  fulfillment_asset integer DEFAULT '0',
  descriptive_asset integer DEFAULT '0',
  creation_date integer DEFAULT '0',
  modification_date integer DEFAULT NULL
);

DROP TABLE commerce_offers;
CREATE TABLE commerce_offers (
  id integer PRIMARY KEY,
  user_id integer,
  name text DEFAULT NULL,
  description text,
  sku text DEFAULT NULL,
  price numeric DEFAULT NULL,
  flexible_price numeric DEFAULT NULL,
  recurring_payment integer DEFAULT '0',
  recurring_interval integer DEFAULT '0',
  creation_date integer DEFAULT '0',
  modification_date integer DEFAULT NULL
);

DROP TABLE commerce_offers_included_items;
CREATE TABLE commerce_offers_included_items (
  id integer PRIMARY KEY,
  offer_id integer,
  item_id integer DEFAULT NULL,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

DROP TABLE commerce_orders;
CREATE TABLE commerce_orders (
  id integer PRIMARY KEY,
  user_id integer,
  customer_user_id integer,
  transaction_id integer,
  order_contents text,
  fulfilled integer DEFAULT '0',
  canceled integer DEFAULT '0',
  physical integer DEFAULT '0',
  digital integer DEFAULT '0',
  notes text,
  country_code text,
  element_id integer,
  cash_session_id text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);

DROP TABLE commerce_transactions;
CREATE TABLE commerce_transactions (
  id integer PRIMARY KEY,
  user_id integer,
  connection_id integer,
  connection_type text,
  service_timestamp integer,
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

COMMIT;
SQLITE;
    } else {
      $query = <<<MYSQL
ALTER TABLE `assets` 
  ADD COLUMN `public_url` varchar(255),
  ADD COLUMN `type` varchar(255) DEFAULT 'storage',
  ADD COLUMN `size` int(11) DEFAULT '0',
  ADD COLUMN `hash`  varchar(255);

DROP TABLE `commerce_assets`;

DROP TABLE `commerce_items`;
CREATE TABLE `commerce_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text,
  `sku` varchar(20) DEFAULT NULL,
  `price` decimal(9,2) DEFAULT NULL,
  `flexible_price` bool DEFAULT '0',
  `digital_fulfillment` bool DEFAULT '0',
  `physical_fulfillment` bool DEFAULT '0',
  `physical_weight` int(11) NOT NULL,
  `physical_width` int(11) NOT NULL,
  `physical_height` int(11) NOT NULL,
  `physical_depth` int(11) NOT NULL,
  `available_units` int(11) NOT NULL DEFAULT '0',
  `variable_pricing` bool DEFAULT '0',
  `fulfillment_asset` int(11) NOT NULL DEFAULT '0',
  `descriptive_asset` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) NOT NULL DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `commerce_offers`;
CREATE TABLE `commerce_offers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text,
  `sku` varchar(20) DEFAULT NULL,
  `price` decimal(9,2) DEFAULT NULL,
  `flexible_price` bool DEFAULT '0',
  `recurring_payment` bool DEFAULT '0',
  `recurring_interval` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) NOT NULL DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `commerce_offers_included_items`;
CREATE TABLE `commerce_offers_included_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `offer_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `commerce_orders`;
CREATE TABLE `commerce_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `customer_user_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `order_contents` text NOT NULL,
  `fulfilled` bool DEFAULT '0',
  `canceled` bool DEFAULT '0',
  `physical` bool DEFAULT '0',
  `digital` bool DEFAULT '0',
  `notes` text NOT NULL,
  `country_code` varchar(255),
  `element_id` int(11),
  `cash_session_id` varchar(24),
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `commerce_transactions`;
CREATE TABLE `commerce_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `connection_id` int(11) NOT NULL,
  `connection_type` varchar(255) NOT NULL,
  `service_timestamp` int(11) NOT NULL,
  `service_transaction_id` varchar(255) NOT NULL DEFAULT '',
  `data_sent` text NOT NULL,
  `data_returned` text NOT NULL,
  `successful` bool DEFAULT '0',
  `gross_price` decimal(9,2) DEFAULT NULL,
  `service_fee` decimal(9,2) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'abandoned',
  `creation_date` int(11) NOT NULL DEFAULT '0',
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
MYSQL;
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

  /***************************
   *
   * 2. ENCODE ANY EXISTING CONNECTION DATA
   *
   ***************************/
  if (!$upgrade_failure) {
    $key = $cash_settings['salt'];
    $query = "SELECT * FROM system_connections";
    try {  
      $pdo->closeCursor();
      $q = $pdo->query($query);
      $q->setFetchMode(PDO::FETCH_ASSOC);
    	$all_connections = $q->fetchAll();
    } catch(PDOException $e) { 
      echo $e->getMessage(); 	
    	$upgrade_failure = true;
    }

    if (!$upgrade_failure) {
      if (is_array($all_connections)) {
      	foreach ($all_connections as $connection) {
      		$data = array(
      			'data' => base64_encode(simpleXOR($connection['data'], $key))
      		);
      		$query = 'UPDATE system_connections SET data=:data WHERE id=' . $connection['id'];
      		$q = $pdo->prepare($query);
      		$q->execute($data);
      	}
      }
    }
  }
}
?>