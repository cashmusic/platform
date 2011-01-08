SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `asst_assets`;

CREATE TABLE `asst_assets` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `parent_id` int(11) default NULL,
  `location` text,
  `seed_settings_id` int(11) default NULL,
  `title` text,
  `description` text,
  `comment` text NOT NULL,
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `user_id` (`user_id`),
  KEY `seed_settings_id` (`seed_settings_id`),
  CONSTRAINT `owner` FOREIGN KEY (`user_id`) REFERENCES `seed_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `parent_child` FOREIGN KEY (`parent_id`) REFERENCES `asst_assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `settings_type` FOREIGN KEY (`seed_settings_id`) REFERENCES `seed_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='InnoDB free: 3072 kB; (`parent_id`) REFER `seed/asts_assets`';


DROP TABLE IF EXISTS `asst_licenses`;

CREATE TABLE `asst_licenses` (
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `fulltext` blob NOT NULL,
  `uri` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cmrc_products`;

CREATE TABLE `cmrc_products` (
  `id` int(11) NOT NULL auto_increment,
  `sku` varchar(20) default NULL,
  `title` varchar(100) default NULL,
  `price` decimal(9,2) default NULL,
  `type` varchar(100) default NULL,
  `beneficiary` varchar(50) default NULL,
  `sub_term_seconds` int(11) default NULL,
  `qty_total` int(11) NOT NULL default '0',
  `creation_date` int(11) NOT NULL default '0',
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `cmrc_transactions`;

CREATE TABLE `cmrc_transactions` (
  `id` int(11) NOT NULL auto_increment,
  `order_timestamp` varchar(24) NOT NULL default '',
  `payer_email` varchar(75) NOT NULL default '',
  `payer_id` varchar(60) NOT NULL default '',
  `payer_firstname` varchar(127) NOT NULL default '',
  `payer_lastname` varchar(127) NOT NULL default '',
  `country` varchar(8) NOT NULL default '',
  `product_sku` varchar(48) NOT NULL default '',
  `product_name` varchar(255) NOT NULL default '',
  `transaction_id` varchar(24) NOT NULL default '',
  `transaction_status` varchar(32) NOT NULL default '',
  `transaction_currency` varchar(8) NOT NULL default '',
  `transaction_amount` int(11) NOT NULL default '0',
  `transaction_fee` decimal(9,2) NOT NULL default '0.00',
  `is_fulfilled` smallint(1) NOT NULL default '0',
  `referral_code` varchar(191) default NULL,
  `nvp_request_json` text,
  `nvp_response_json` text,
  `nvp_details_json` text,
  `creation_date` int(11) NOT NULL default '0',
  `modification_date` int(11) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `emal_addresses`;

CREATE TABLE `emal_addresses` (
  `id` int(11) NOT NULL auto_increment,
  `list_id` int(11) default NULL,
  `email_address` text,
  `name` varchar(255) NOT NULL default '',
  `verification_code` text,
  `verified` int(11) default '0',
  `initial_comment` text,
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `emal_lists`;

CREATE TABLE `emal_lists` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `live_dates`;

CREATE TABLE `live_dates` (
  `id` int(11) NOT NULL auto_increment,
  `date` int(11) default NULL,
  `artist_id` int(11) default NULL,
  `venue_id` int(11) default NULL,
  `publish` tinyint(1) default NULL,
  `cancelled` tinyint(1) default NULL,
  `comments` text,
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `live_venues`;

CREATE TABLE `live_venues` (
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `address1` text,
  `address2` text,
  `city` text,
  `region` text,
  `country` text,
  `postalcode` text,
  `website` text,
  `phone` text,
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lock_codes`;

CREATE TABLE `lock_codes` (
  `id` int(11) NOT NULL auto_increment,
  `uid` tinytext,
  `asset_id` int(11) default NULL,
  `claim_date` int(11) default NULL,
  `creation_date` int(11) default '0',
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `asset_id` (`asset_id`),
  CONSTRAINT `associated_asset` FOREIGN KEY (`asset_id`) REFERENCES `asst_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lock_passwords`;

CREATE TABLE `lock_passwords` (
  `id` int(11) NOT NULL auto_increment,
  `password` text,
  `asset_id` int(11) default NULL,
  `creation_date` int(11) default '0',
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `asset_id` (`asset_id`),
  CONSTRAINT `lock_passwords_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `asst_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `seed_organizations`;

CREATE TABLE `seed_organizations` (
  `id` int(11) NOT NULL auto_increment,
  `name` tinytext,
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `seed_organizations_admin`;

CREATE TABLE `seed_organizations_admin` (
  `id` int(11) NOT NULL auto_increment,
  `organization_id` int(11) NOT NULL default '0',
  `login_id` int(11) NOT NULL default '0',
  `organization_admin` int(11) default '0',
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `seed_settings`;

CREATE TABLE `seed_settings` (
  `id` int(11) NOT NULL auto_increment,
  `name` text,
  `type` text NOT NULL,
  `data` text NOT NULL,
  `isdefault` tinyint(4) NOT NULL default '0',
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `seed_users`;

CREATE TABLE `seed_users` (
  `id` int(11) NOT NULL auto_increment,
  `email_address` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `password` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `username` varchar(32) NOT NULL default '',
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
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `email` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `seed_users_resetpassword`;

CREATE TABLE `seed_users_resetpassword` (
  `id` int(11) NOT NULL auto_increment,
  `time_requested` int(11) NOT NULL default '0',
  `random_key` tinytext NOT NULL,
  `login_id` int(11) NOT NULL default '0',
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `seed_users_tags`;

CREATE TABLE `seed_users_tags` (
  `id` int(11) NOT NULL auto_increment,
  `scope_table_name` varchar(64) NOT NULL default '',
  `scope_table_id` int(11) NOT NULL default '0',
  `login_id` int(11) NOT NULL default '0',
  `tag` text,
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `strm_permissions`;

CREATE TABLE `strm_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `login_id` int(11) NOT NULL default '0',
  `stream_id` int(11) NOT NULL default '0',
  `allowed_logins` int(11) NOT NULL default '-1',
  `total_logins` int(11) NOT NULL default '0',
  `date_expires` int(11) NOT NULL default '-1',
  `last_timestamp` int(11) default '0',
  `last_ip` tinytext,
  `stream_password` tinytext,
  `added_by` int(11) NOT NULL default '0',
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `login_id` (`login_id`,`stream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `strm_streams`;

CREATE TABLE `strm_streams` (
  `id` int(11) NOT NULL auto_increment,
  `title` tinytext NOT NULL,
  `artist_id` int(11) NOT NULL default '0',
  `organization_id` int(11) NOT NULL default '0',
  `primary_url` tinytext,
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `strm_streams_admin`;

CREATE TABLE `strm_streams_admin` (
  `id` int(11) NOT NULL auto_increment,
  `stream_id` int(11) NOT NULL default '0',
  `login_id` int(11) NOT NULL default '0',
  `view` text,
  `permission` int(11) NOT NULL default '0',
  `creation_date` int(11) default NULL,
  `modification_date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET FOREIGN_KEY_CHECKS = 1;
