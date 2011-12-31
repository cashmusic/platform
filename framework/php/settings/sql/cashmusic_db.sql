SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `assets`;

CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `location` text,
  `connection_id` int(11) DEFAULT NULL,
  `title` text,
  `description` text,
  `public_status` bool DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `asst_asets_parent_id` (`parent_id`),
  KEY `assets_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `assets_licenses`;

CREATE TABLE `assets_licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `fulltext` blob NOT NULL,
  `uri` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `commerce_products`;

CREATE TABLE `commerce_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(20) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `price` decimal(9,2) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `beneficiary` varchar(50) DEFAULT NULL,
  `sub_term_seconds` int(11) DEFAULT NULL,
  `qty_total` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) NOT NULL DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `commerce_transactions`;

CREATE TABLE `commerce_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_timestamp` varchar(24) NOT NULL DEFAULT '',
  `payer_email` varchar(75) NOT NULL DEFAULT '',
  `payer_id` varchar(60) NOT NULL DEFAULT '',
  `payer_firstname` varchar(127) NOT NULL DEFAULT '',
  `payer_lastname` varchar(127) NOT NULL DEFAULT '',
  `country` varchar(8) NOT NULL DEFAULT '',
  `product_sku` varchar(48) NOT NULL DEFAULT '',
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `transaction_id` varchar(24) NOT NULL DEFAULT '',
  `transaction_status` varchar(32) NOT NULL DEFAULT '',
  `transaction_currency` varchar(8) NOT NULL DEFAULT '',
  `transaction_amount` int(11) NOT NULL DEFAULT '0',
  `transaction_fee` decimal(9,2) NOT NULL DEFAULT '0.00',
  `is_fulfilled` smallint(1) NOT NULL DEFAULT '0',
  `referral_code` varchar(191) DEFAULT NULL,
  `nvp_request_json` text,
  `nvp_response_json` text,
  `nvp_details_json` text,
  `creation_date` int(11) NOT NULL DEFAULT '0',
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `calendar_events`;

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `venue_id` int(11) DEFAULT NULL,
  `published` tinyint(1) DEFAULT NULL,
  `cancelled` tinyint(1) DEFAULT NULL,
  `purchase_url` text,
  `comments` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendar_events_user_id` (`user_id`)
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


DROP TABLE IF EXISTS `system_lock_codes`;

CREATE TABLE `system_lock_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` tinytext,
  `element_id` int(11) DEFAULT NULL,
  `claim_date` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_lock_codes_element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `system_lock_passwords`;

CREATE TABLE `system_lock_passwords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` text,
  `element_id` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_lock_passwords_element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `assets_analytics`;

CREATE TABLE `assets_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL DEFAULT '0',
  `element_id` int(11) DEFAULT NULL,
  `access_time` int(11) NOT NULL,
  `client_ip` varchar(39) NOT NULL,
  `client_proxy` varchar(39) NOT NULL,
  `cash_session_id` varchar(24) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `assets_analytics_asset_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `system_lock_permissions`;

CREATE TABLE `system_lock_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_list_id` int(11) NOT NULL DEFAULT '0',
  `element_id` int(11) NOT NULL DEFAULT '0',
  `allowed_logins` int(11) NOT NULL DEFAULT '-1',
  `total_logins` int(11) NOT NULL DEFAULT '0',
  `date_expires` int(11) NOT NULL DEFAULT '-1',
  `element_password` tinytext,
  `added_by` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_lock_permissions_login_id` (`user_list_id`,`element_id`),
  KEY `system_lock_permissions_element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `people`;

CREATE TABLE `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_address` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `password` char(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `username` varchar(32) NOT NULL DEFAULT '',
  `display_name` tinytext,
  `first_name` tinytext,
  `last_name` tinytext,
  `organization` tinytext,
  `address_line1` tinytext,
  `address_line2` tinytext,
  `address_city` tinytext,
  `address_region` tinytext,
  `address_postalcode` tinytext,
  `address_country` tinytext,
  `is_admin` bool NOT NULL DEFAULT '0',
  `api_key` char(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `api_secret` char(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email_address`)
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


DROP TABLE IF EXISTS `people_resetpassword`;

CREATE TABLE `people_resetpassword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_requested` int(11) NOT NULL DEFAULT '0',
  `random_key` tinytext NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `elements`;

CREATE TABLE `elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` text,
  `type` text NOT NULL,
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
  `access_action` text NOT NULL,
  `access_data` text NOT NULL,
  `access_time` int(11) NOT NULL,
  `client_ip` varchar(39) NOT NULL,
  `client_proxy` varchar(39) NOT NULL,
  `cash_session_id` varchar(24) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `elements_analytics_element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `system_connections`;

CREATE TABLE `system_connections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `type` text NOT NULL,
  `data` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `system_analytics`;

CREATE TABLE `system_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` text NOT NULL,
  `data` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `scope_table_alias` text DEFAULT NULL,
  `scope_table_id` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
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


DROP TABLE IF EXISTS `calendar_guestlist`;

CREATE TABLE `calendar_guestlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(128) NOT NULL,
  `guest_name` text,
  `total_attendees` int(11) NOT NULL DEFAULT '1',
  `comment` text NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `system_metadata`;

CREATE TABLE `system_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scope_table_alias` varchar(64) NOT NULL DEFAULT '',
  `scope_table_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `type` text,
  `value` text NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_metadata_scope_table` (`scope_table_alias`,`scope_table_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET FOREIGN_KEY_CHECKS = 1;
