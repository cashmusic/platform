-- 
-- CASH Music platform
-- flavor: MySQL
-- schema version: 8
-- modified: April 22, 2013

SET FOREIGN_KEY_CHECKS = 0;

-- 
-- 
-- Section: ASSETS
-- 
DROP TABLE IF EXISTS `assets`;
CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `location` varchar(255),
  `public_url` varchar(255),
  `connection_id` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT 'file',
  `title` varchar(255),
  `description` text,
  `metadata` text,
  `public_status` bool DEFAULT '0',
  `size` int(11) DEFAULT '0',
  `hash`  varchar(255),
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `asst_asets_parent_id` (`parent_id`),
  KEY `assets_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `assets_analytics`;
CREATE TABLE `assets_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL DEFAULT '0',
  `element_id` int(11) DEFAULT NULL,
  `access_time` int(11) NOT NULL,
  `client_ip` varchar(39) NOT NULL,
  `client_proxy` varchar(39) NOT NULL,
  `cash_session_id` varchar(255) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `assets_analytics_asset_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `assets_analytics_basic`;
CREATE TABLE `assets_analytics_basic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL DEFAULT '0',
  `total` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 
-- 
-- Section: CALENDAR
-- 
DROP TABLE IF EXISTS `calendar_events`;
CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `venue_id` int(11) DEFAULT NULL,
  `published` bool DEFAULT NULL,
  `cancelled` bool DEFAULT NULL,
  `purchase_url` varchar(255),
  `comments` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendar_events_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `calendar_guestlist`;
CREATE TABLE `calendar_guestlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(128) NOT NULL,
  `guest_name` varchar(255),
  `total_attendees` int(11) NOT NULL DEFAULT '1',
  `comment` text NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `calendar_venues`;
CREATE TABLE `calendar_venues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `address1` text,
  `address2` text,
  `city` text,
  `region` text,
  `country` text,
  `postalcode` text,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `url` text,
  `phone` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 
-- 
-- Section: COMMERCE
-- 
DROP TABLE IF EXISTS `commerce_items`;
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

DROP TABLE IF EXISTS `commerce_offers`;
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

DROP TABLE IF EXISTS `commerce_offers_included_items`;
CREATE TABLE `commerce_offers_included_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `offer_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `commerce_orders`;
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

DROP TABLE IF EXISTS `commerce_transactions`;
CREATE TABLE `commerce_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `connection_id` int(11) NOT NULL,
  `connection_type` varchar(255) NOT NULL,
  `service_timestamp` varchar(255) NOT NULL,
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


-- 
-- 
-- Section: ELEMENTS
-- 
DROP TABLE IF EXISTS `elements`;
CREATE TABLE `elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `template_id` int(11) DEFAULT '0',
  `name` varchar(255),
  `type` varchar(255) NOT NULL,
  `options` text,
  `license_id` int(11) DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `elements_analytics`;
CREATE TABLE `elements_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL,
  `access_method` varchar(24) NOT NULL,
  `access_location` text NOT NULL,
  `access_action` varchar(255) NOT NULL,
  `access_data` text NOT NULL,
  `access_time` int(11) NOT NULL,
  `client_ip` varchar(39) NOT NULL,
  `client_proxy` varchar(39) NOT NULL,
  `cash_session_id` varchar(255) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `elements_analytics_element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `elements_analytics_basic`;
CREATE TABLE `elements_analytics_basic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `total` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 
-- 
-- Section: PEOPLE
-- 
DROP TABLE IF EXISTS `people`;
CREATE TABLE `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_address` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(32) NOT NULL DEFAULT '',
  `display_name` varchar(255),
  `first_name` varchar(255),
  `last_name` varchar(255),
  `organization` varchar(255),
  `address_line1` varchar(255),
  `address_line2` varchar(255),
  `address_city` varchar(255),
  `address_region` varchar(255),
  `address_postalcode` varchar(255),
  `address_country` varchar(255),
  `is_admin` bool NOT NULL DEFAULT '0',
  `data` text,
  `api_key` char(64) DEFAULT '',
  `api_secret` char(64) DEFAULT '',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `people_analytics`;
CREATE TABLE `people_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `element_id` int(11) DEFAULT NULL,
  `access_time` int(11) NOT NULL,
  `client_ip` varchar(39) NOT NULL,
  `client_proxy` varchar(39) NOT NULL,
  `login_method` varchar(15) DEFAULT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `people_analytics_basic`;
CREATE TABLE `people_analytics_basic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `total` int(11) DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `people_contacts`;
CREATE TABLE `people_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `first_name` varchar(255),
  `last_name` varchar(255),
  `organization` varchar(255),
  `address_line1` varchar(255),
  `address_line2` varchar(255),
  `address_city` varchar(255),
  `address_region` varchar(255),
  `address_postalcode` varchar(255),
  `address_country` varchar(255),
  `phone` varchar(255),
  `notes` text,
  `links` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `people_lists`;
CREATE TABLE `people_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `description` text,
  `user_id` int(11) NOT NULL,
  `connection_id` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `people_lists_members`;
CREATE TABLE `people_lists_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `verification_code` text,
  `verified` bool DEFAULT '0',
  `active` bool DEFAULT '1',
  `initial_comment` text,
  `additional_data` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `people_lists_members_user_id` (`user_id`),
  KEY `people_lists_members_list_id` (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `people_mailings`;
CREATE TABLE `people_mailings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `connection_id` int(11) NOT NULL DEFAULT '0',
  `list_id` int(11) NOT NULL DEFAULT '0',
  `template_id` int(11) DEFAULT '0',
  `subject` varchar(255),
  `html_content` mediumtext,
  `text_content` mediumtext,
  `send_date` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `people_mailings_analytics`;
CREATE TABLE `people_mailings_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailing_id` int(11) NOT NULL DEFAULT '0',
  `sends` int(11) DEFAULT '0',
  `opens_total` int(11) DEFAULT '0',
  `opens_unique` int(11) DEFAULT '0',
  `opens_mobile` int(11) DEFAULT '0',
  `opens_country` mediumtext,
  `opens_ids` mediumtext,
  `clicks` int(11) DEFAULT '0',
  `clicks_urls` text,
  `failures` int(11) DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `people_resetpassword`;
CREATE TABLE `people_resetpassword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 
-- 
-- Section: SYSTEM
-- 
DROP TABLE IF EXISTS `system_analytics`;
CREATE TABLE `system_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `filter` varchar(255) NOT NULL,
  `primary_value` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `scope_table_alias` text DEFAULT NULL,
  `scope_table_id` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `system_connections`;
CREATE TABLE `system_connections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `type` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `system_licenses`;
CREATE TABLE `system_licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `fulltext` blob NOT NULL,
  `url` varchar(255) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `system_lock_codes`;
CREATE TABLE `system_lock_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255),
  `scope_table_alias` varchar(255) DEFAULT 'elements',
  `scope_table_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `claim_date` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_lock_codes_uid` (`uid`),
  KEY `system_lock_codes_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `system_metadata`;
CREATE TABLE `system_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scope_table_alias` varchar(64) NOT NULL DEFAULT '',
  `scope_table_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(255),
  `value` text NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_metadata_scope_table` (`scope_table_alias`,`scope_table_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `system_templates`;
CREATE TABLE `system_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255),
  `name` varchar(255),
  `user_id` int(11) NOT NULL,
  `template` mediumtext,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
