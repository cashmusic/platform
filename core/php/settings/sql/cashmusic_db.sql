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
  `comment` text NOT NULL,
  `public_status` bit(1) DEFAULT b'0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `user_id` (`user_id`),
  KEY `seed_settings_id` (`settings_id`),
  KEY `settings_id` (`settings_id`),
  CONSTRAINT `settings_type` FOREIGN KEY (`settings_id`) REFERENCES `cash_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `owner` FOREIGN KEY (`user_id`) REFERENCES `cash_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `parent_child` FOREIGN KEY (`parent_id`) REFERENCES `asst_assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='InnoDB free: 3072 kB; (`parent_id`) REFER `seed/asts_assets`';


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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `emal_addresses`;

CREATE TABLE `emal_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) DEFAULT NULL,
  `email_address` text,
  `name` varchar(255) NOT NULL DEFAULT '',
  `verification_code` text,
  `verified` int(11) DEFAULT '0',
  `initial_comment` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `emal_lists`;

CREATE TABLE `emal_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
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
  PRIMARY KEY (`id`)
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
  `asset_id` int(11) DEFAULT NULL,
  `claim_date` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  CONSTRAINT `associated_asset` FOREIGN KEY (`asset_id`) REFERENCES `asst_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lock_passwords`;

CREATE TABLE `lock_passwords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` text,
  `asset_id` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  CONSTRAINT `lock_passwords_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `asst_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `strm_permissions`;

CREATE TABLE `strm_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login_id` int(11) NOT NULL DEFAULT '0',
  `stream_id` int(11) NOT NULL DEFAULT '0',
  `allowed_logins` int(11) NOT NULL DEFAULT '-1',
  `total_logins` int(11) NOT NULL DEFAULT '0',
  `date_expires` int(11) NOT NULL DEFAULT '-1',
  `last_timestamp` int(11) DEFAULT '0',
  `last_ip` tinytext,
  `stream_password` tinytext,
  `added_by` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_id` (`login_id`,`stream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `strm_streams`;

CREATE TABLE `strm_streams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` tinytext NOT NULL,
  `artist_id` int(11) NOT NULL DEFAULT '0',
  `organization_id` int(11) NOT NULL DEFAULT '0',
  `primary_url` tinytext,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `strm_streams_admin`;

CREATE TABLE `strm_streams_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL DEFAULT '0',
  `login_id` int(11) NOT NULL DEFAULT '0',
  `view` text,
  `permission` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cash_users_tags`;

CREATE TABLE `cash_users_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scope_table_name` varchar(64) NOT NULL DEFAULT '',
  `scope_table_id` int(11) NOT NULL DEFAULT '0',
  `login_id` int(11) NOT NULL DEFAULT '0',
  `tag` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cash_settings`;

CREATE TABLE `cash_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `type` text NOT NULL,
  `data` text NOT NULL,
  `isdefault` tinyint(4) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cash_users`;

CREATE TABLE `cash_users` (
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
  `comments` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email_address`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cash_organizations`;

CREATE TABLE `cash_organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cash_elements`;

CREATE TABLE `cash_elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` text,
  `type` text NOT NULL,
  `options` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cash_users_resetpassword`;

CREATE TABLE `cash_users_resetpassword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_requested` int(11) NOT NULL DEFAULT '0',
  `random_key` tinytext NOT NULL,
  `login_id` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cash_organizations_admin`;

CREATE TABLE `cash_organizations_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) NOT NULL DEFAULT '0',
  `login_id` int(11) NOT NULL DEFAULT '0',
  `organization_admin` int(11) DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET FOREIGN_KEY_CHECKS = 1;
