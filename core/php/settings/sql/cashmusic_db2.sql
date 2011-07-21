-- MySQL dump 10.13  Distrib 5.5.9, for osx10.4 (i386)
--
-- Host: localhost    Database: seed
-- ------------------------------------------------------
-- Server version	5.5.9

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asst_analytics`
--

DROP TABLE IF EXISTS `asst_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asst_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL DEFAULT '0',
  `element_id` int(11) NOT NULL,
  `access_time` int(11) NOT NULL,
  `client_ip` varchar(15) NOT NULL,
  `client_proxy` varchar(15) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asst_analytics`
--

LOCK TABLES `asst_analytics` WRITE;
/*!40000 ALTER TABLE `asst_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `asst_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asst_assets`
--

DROP TABLE IF EXISTS `asst_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `license_id` int(11) DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `user_id` (`user_id`),
  KEY `seed_settings_id` (`settings_id`),
  KEY `settings_id` (`settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='InnoDB free: 3072 kB; (`parent_id`) REFER `seed/asts_assets`';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asst_assets`
--

LOCK TABLES `asst_assets` WRITE;
/*!40000 ALTER TABLE `asst_assets` DISABLE KEYS */;
INSERT INTO `asst_assets` VALUES (1,4,NULL,'users/badbooks/BadBooks_YouWouldntHaveToAsk.mp3',7,'\"You Wouldn\'t Have To Ask\" MP3','320kbps MP3','','\0',0,1289452898,0);
/*!40000 ALTER TABLE `asst_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asst_licenses`
--

DROP TABLE IF EXISTS `asst_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asst_licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `fulltext` blob NOT NULL,
  `uri` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asst_licenses`
--

LOCK TABLES `asst_licenses` WRITE;
/*!40000 ALTER TABLE `asst_licenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `asst_licenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `base_settings`
--

DROP TABLE IF EXISTS `base_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `base_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `type` text NOT NULL,
  `data` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `base_settings`
--

LOCK TABLES `base_settings` WRITE;
/*!40000 ALTER TABLE `base_settings` DISABLE KEYS */;
INSERT INTO `base_settings` VALUES (7,'Main CASH S3 Settings','com.amazon','{\"key\":\"0JQ2XC5FJRQA01YNSR82\",\"secret\":\"20vnJRjKPp\\/HA67c\\/cIm33IHnv3F8oJFlDXlAh0z\",\"bucket\":\"cashmusic\"}',4,1288236925,NULL),(8,'MailChimp Test','com.mailchimp','{\"settings_key\":\"testkey\"}',4,1310883320,NULL),(9,'Twitter Connection','com.twitter','{\"settings_key\":\"key\",\"settings_secret\":\"secret\"}',4,1310883400,NULL);
/*!40000 ALTER TABLE `base_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `base_tags`
--

DROP TABLE IF EXISTS `base_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `base_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scope_table_alias` varchar(64) NOT NULL DEFAULT '',
  `scope_table_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `tag` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `base_tags`
--

LOCK TABLES `base_tags` WRITE;
/*!40000 ALTER TABLE `base_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `base_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cmrc_products`
--

DROP TABLE IF EXISTS `cmrc_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cmrc_products`
--

LOCK TABLES `cmrc_products` WRITE;
/*!40000 ALTER TABLE `cmrc_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `cmrc_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cmrc_transactions`
--

DROP TABLE IF EXISTS `cmrc_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cmrc_transactions`
--

LOCK TABLES `cmrc_transactions` WRITE;
/*!40000 ALTER TABLE `cmrc_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `cmrc_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elmt_analytics`
--

DROP TABLE IF EXISTS `elmt_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `elmt_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL,
  `access_method` varchar(24) NOT NULL,
  `access_location` text NOT NULL,
  `access_time` int(11) NOT NULL,
  `client_ip` varchar(15) NOT NULL,
  `client_proxy` varchar(15) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elmt_analytics`
--

LOCK TABLES `elmt_analytics` WRITE;
/*!40000 ALTER TABLE `elmt_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `elmt_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elmt_elements`
--

DROP TABLE IF EXISTS `elmt_elements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `elmt_elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` text,
  `type` text NOT NULL,
  `options` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elmt_elements`
--

LOCK TABLES `elmt_elements` WRITE;
/*!40000 ALTER TABLE `elmt_elements` DISABLE KEYS */;
INSERT INTO `elmt_elements` VALUES (9,4,'Bad Books YWHTA EFD','emailfordownload','{\"message_invalid_email\":\"Sorry, that email address wasn\'t valid. Please try again.\",\"message_privacy\":\"We won\'t share, sell, or be jerks with your email address.\",\"message_success\":\"Thanks! You\'re all signed up. Here\'s your download:\",\"emal_list_id\":\"1\",\"asset_id\":\"1\",\"comment_or_radio\":\"none\"}',1295132755,NULL),(10,4,'Bad Books Email promo','emailfordownload','{\"message_invalid_email\":\"That email address wasn\'t valid. Try again.\",\"message_privacy\":\"We won\'t share, sell, or be jerks with your email address. Promise.\",\"message_success\":\"Thanks! You\'re all signed up. Here\'s your download:\",\"emal_list_id\":\"1\",\"asset_id\":\"1\",\"comment_or_radio\":\"none\"}',1295306566,NULL),(12,4,'A much longer, more descriptive title','emailfordownload','{\"message_invalid_email\":\"Sorry, that email address wasn\'t valid. Please try again.\",\"message_privacy\":\"We won\'t share, sell, or be jerks with your email address.\",\"message_success\":\"Thanks! You\'re all signed up. Here\'s your download:\",\"emal_list_id\":\"1\",\"asset_id\":\"1\",\"comment_or_radio\":\"none\"}',1303890019,1304481100),(13,4,'New Email Download thinger','emailfordownload','{\"message_invalid_email\":\"Sorry, that email address wasn\'t valid. Please try again.\",\"message_privacy\":\"We won\'t share, sell, or be jerks with your email address.\",\"message_success\":\"Thanks! You\'re all signed up. Here\'s your download:\",\"emal_list_id\":\"1\",\"asset_id\":\"1\",\"comment_or_radio\":\"none\"}',1304631549,NULL);
/*!40000 ALTER TABLE `elmt_elements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `live_events`
--

DROP TABLE IF EXISTS `live_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `live_events`
--

LOCK TABLES `live_events` WRITE;
/*!40000 ALTER TABLE `live_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `live_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `live_venues`
--

DROP TABLE IF EXISTS `live_venues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `live_venues`
--

LOCK TABLES `live_venues` WRITE;
/*!40000 ALTER TABLE `live_venues` DISABLE KEYS */;
/*!40000 ALTER TABLE `live_venues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lock_codes`
--

DROP TABLE IF EXISTS `lock_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lock_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` tinytext,
  `asset_id` int(11) DEFAULT NULL,
  `claim_date` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=264 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lock_codes`
--

LOCK TABLES `lock_codes` WRITE;
/*!40000 ALTER TABLE `lock_codes` DISABLE KEYS */;
INSERT INTO `lock_codes` VALUES (228,'rn32mc3755',1,NULL,1290304139,NULL),(229,'3n77ip33d2',1,NULL,1290304144,NULL),(230,'bn6ch337mk',1,NULL,1290304147,NULL),(231,'zn4hge33vd',1,NULL,1290304147,NULL),(232,'2n4hge33v9',1,NULL,1290304147,NULL),(233,'in3hee33v4',1,NULL,1290304147,NULL),(234,'fn3hge33vg',1,NULL,1290304149,NULL),(235,'kn7ner37dc',1,NULL,1290304149,NULL),(236,'dn8ner37de',1,NULL,1290304150,NULL),(237,'fn7tc533mr',1,NULL,1290304150,NULL),(238,'jn4td533mi',1,NULL,1290304150,NULL),(239,'wn6tb533mx',1,NULL,1290304150,NULL),(240,'nn7td533m7',1,NULL,1290304150,NULL),(241,'qn7td533m5',1,NULL,1290304150,NULL),(242,'fn7tc533m2',1,NULL,1290304151,NULL),(243,'xn6yag37va',1,NULL,1290304151,NULL),(244,'kn3yag37v2',1,NULL,1290304153,NULL),(245,'3n26xt33dk',1,NULL,1290304153,NULL),(246,'rn265t33dn',1,NULL,1290304153,NULL),(247,'jn76yt33d8',1,NULL,1290304153,NULL),(248,'4n76yt33ds',1,NULL,1290304154,NULL),(249,'qn4bv737m6',1,NULL,1290304154,NULL),(250,'un4bx737ms',1,NULL,1290304154,NULL),(251,'hn4bx737m4',1,NULL,1290304154,NULL),(252,'bn3bx737mc',1,NULL,1290304154,NULL),(253,'sn3bv737mh',1,NULL,1290304154,NULL),(254,'vn6bx737me',1,NULL,1290304155,NULL),(255,'zn3gvi33v4',1,NULL,1290304155,NULL),(256,'cn2gui33v3',1,NULL,1290304155,NULL),(257,'jn6mtv37de',1,NULL,1290304155,NULL),(258,'7n7mtv37dp',1,NULL,1290304155,NULL),(259,'vn8mrv37d6',1,NULL,1290304155,NULL),(260,'yn8msv37fu',1,NULL,1290305228,NULL),(261,'6n7sr933qk',1,NULL,1290305904,NULL),(262,'mn3xnk37ac',1,NULL,1290306033,NULL),(263,'xn75jx33jc',1,NULL,1290306326,NULL);
/*!40000 ALTER TABLE `lock_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lock_passwords`
--

DROP TABLE IF EXISTS `lock_passwords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lock_passwords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` text,
  `asset_id` int(11) DEFAULT NULL,
  `creation_date` int(11) DEFAULT '0',
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lock_passwords`
--

LOCK TABLES `lock_passwords` WRITE;
/*!40000 ALTER TABLE `lock_passwords` DISABLE KEYS */;
INSERT INTO `lock_passwords` VALUES (1,'download',1,1289550495,NULL);
/*!40000 ALTER TABLE `lock_passwords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lock_permissions`
--

DROP TABLE IF EXISTS `lock_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lock_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  KEY `login_id` (`user_list_id`,`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lock_permissions`
--

LOCK TABLES `lock_permissions` WRITE;
/*!40000 ALTER TABLE `lock_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `lock_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_lists`
--

DROP TABLE IF EXISTS `user_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `description` text,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_lists`
--

LOCK TABLES `user_lists` WRITE;
/*!40000 ALTER TABLE `user_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_lists_members`
--

DROP TABLE IF EXISTS `user_lists_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_lists_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_lists_members`
--

LOCK TABLES `user_lists_members` WRITE;
/*!40000 ALTER TABLE `user_lists_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_lists_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_resetpassword`
--

DROP TABLE IF EXISTS `user_resetpassword`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_resetpassword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_requested` int(11) NOT NULL DEFAULT '0',
  `random_key` tinytext NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_resetpassword`
--

LOCK TABLES `user_resetpassword` WRITE;
/*!40000 ALTER TABLE `user_resetpassword` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_resetpassword` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_users`
--

DROP TABLE IF EXISTS `user_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `comments` text,
  `verification_code` text NOT NULL,
  `verified` bit(1) NOT NULL,
  `is_admin` bit(1) NOT NULL DEFAULT b'0',
  `creation_date` int(11) DEFAULT NULL,
  `modification_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email_address`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_users`
--

LOCK TABLES `user_users` WRITE;
/*!40000 ALTER TABLE `user_users` DISABLE KEYS */;
INSERT INTO `user_users` VALUES (4,'jesse@cashmusic.org','8eca89bd0939a769056833a34d345bfbd06d91279a3d9f48b2ce78c5368c7971','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','\0','',1302418480,NULL);
/*!40000 ALTER TABLE `user_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-07-21 15:04:32
