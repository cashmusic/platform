SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `asst_assets`;

CREATE TABLE `asst_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `location` text,
  `settings_id` int(11) DEFAULT NULL,
  `title` text,
  `description` text,
  `public_status` bit(1) DEFAULT b'0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `asst_asets_parent_id` (`parent_id`),
  KEY `asst_assets_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `asst_licenses`;

CREATE TABLE `asst_licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `fulltext` blob NOT NULL,
  `uri` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cmrc_products`;

CREATE TABLE `cmrc_products` (
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


DROP TABLE IF EXISTS `cmrc_transactions`;

CREATE TABLE `cmrc_transactions` (
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


DROP TABLE IF EXISTS `live_events`;

CREATE TABLE `live_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `venue_id` int(11) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `cancelled` tinyint(1) DEFAULT NULL,
  `comments` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `live_events_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `live_venues`;

CREATE TABLE `live_venues` (
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
  `website` text,
  `phone` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lock_codes`;

CREATE TABLE `lock_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` tinytext,
  `element_id` int(11) DEFAULT NULL,
  `claim_date` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lock_codes_element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lock_passwords`;

CREATE TABLE `lock_passwords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` text,
  `element_id` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lock_passwords_element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `asst_analytics`;

CREATE TABLE `asst_analytics` (
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
  KEY `asst_analytics_asset_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lock_permissions`;

CREATE TABLE `lock_permissions` (
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
  KEY `lock_permissions_login_id` (`user_list_id`,`element_id`),
  KEY `lock_permissions_element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `user_users`;

CREATE TABLE `user_users` (
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
  `is_admin` bit(1) NOT NULL DEFAULT b'0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `user_lists`;

CREATE TABLE `user_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `description` text,
  `user_id` int(11) NOT NULL,
  `settings_id` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `user_resetpassword`;

CREATE TABLE `user_resetpassword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_requested` int(11) NOT NULL DEFAULT '0',
  `random_key` tinytext NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `elmt_elements`;

CREATE TABLE `elmt_elements` (
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


DROP TABLE IF EXISTS `elmt_analytics`;

CREATE TABLE `elmt_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL,
  `access_method` varchar(24) NOT NULL,
  `access_location` text NOT NULL,
  `lock_method_table` varchar(24) NOT NULL,
  `lock_method_id` int(11) NOT NULL,
  `access_time` int(11) NOT NULL,
  `client_ip` varchar(39) NOT NULL,
  `client_proxy` varchar(39) NOT NULL,
  `cash_session_id` varchar(24) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `elmt_analytics_element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `base_settings`;

CREATE TABLE `base_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `type` text NOT NULL,
  `data` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `user_lists_members`;

CREATE TABLE `user_lists_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `verification_code` text NOT NULL,
  `verified` bit(1) NOT NULL,
  `initial_comment` text NOT NULL,
  `additional_data` text NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_lists_members_user_id` (`user_id`),
  KEY `user_lists_members_list_id` (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `live_guestlist`;

CREATE TABLE `live_guestlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(128) NOT NULL,
  `guest_name` text,
  `total_attendees` int(11) NOT NULL DEFAULT '1',
  `comment` text NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `base_metadata`;

CREATE TABLE `base_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scope_table_alias` varchar(64) NOT NULL DEFAULT '',
  `scope_table_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `type` text,
  `value` text NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `base_metadata_scope_table` (`scope_table_alias`,`scope_table_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET FOREIGN_KEY_CHECKS = 1;
