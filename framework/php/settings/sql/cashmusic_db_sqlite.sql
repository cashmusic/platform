-- 
-- Created by SQL::Translator::Producer::SQLite
-- Created on Wed Aug 24 20:50:48 2011
-- 

BEGIN TRANSACTION;

--
-- Table: asst_assets
--
CREATE TABLE asst_assets (
  id INTEGER PRIMARY KEY,
  user_id int(11) DEFAULT NULL,
  parent_id int(11) DEFAULT NULL,
  location text,
  settings_id int(11) DEFAULT NULL,
  title text,
  description text,
  public_status bool DEFAULT '0',
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT 0
);

CREATE INDEX asst_asets_parent_id ON asst_assets (parent_id);

CREATE INDEX asst_assets_user_id ON asst_assets (user_id);

--
-- Table: asst_licenses
--
CREATE TABLE asst_licenses (
  id INTEGER PRIMARY KEY,
  name text,
  description text,
  fulltext blob,
  uri text
);

--
-- Table: cmrc_products
--
CREATE TABLE cmrc_products (
  id INTEGER PRIMARY KEY,
  sku varchar(20) DEFAULT NULL,
  title varchar(100) DEFAULT NULL,
  price decimal(9,2) DEFAULT NULL,
  type varchar(100) DEFAULT NULL,
  beneficiary varchar(50) DEFAULT NULL,
  sub_term_seconds int(11) DEFAULT NULL,
  qty_total int(11) DEFAULT 0,
  creation_date int(11) DEFAULT 0,
  modification_date int(11) DEFAULT NULL
);

--
-- Table: cmrc_transactions
--
CREATE TABLE cmrc_transactions (
  id INTEGER PRIMARY KEY,
  order_timestamp varchar(24) DEFAULT '',
  payer_email varchar(75) DEFAULT '',
  payer_id varchar(60) DEFAULT '',
  payer_firstname varchar(127) DEFAULT '',
  payer_lastname varchar(127) DEFAULT '',
  country varchar(8) DEFAULT '',
  product_sku varchar(48) DEFAULT '',
  product_name varchar(255) DEFAULT '',
  transaction_id varchar(24) DEFAULT '',
  transaction_status varchar(32) DEFAULT '',
  transaction_currency varchar(8) DEFAULT '',
  transaction_amount int(11) DEFAULT 0,
  transaction_fee decimal(9,2) DEFAULT 0.00,
  is_fulfilled smallint(1) DEFAULT 0,
  referral_code varchar(191) DEFAULT NULL,
  nvp_request_json text,
  nvp_response_json text,
  nvp_details_json text,
  creation_date int(11) DEFAULT 0,
  modification_date int(11) DEFAULT 0
);

--
-- Table: live_events
--
CREATE TABLE live_events (
  id INTEGER PRIMARY KEY,
  date int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  venue_id int(11) DEFAULT NULL,
  publish tinyint(1) DEFAULT NULL,
  cancelled tinyint(1) DEFAULT NULL,
  comments text,
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT NULL
);

CREATE INDEX live_events_user_id ON live_events (user_id);

--
-- Table: live_venues
--
CREATE TABLE live_venues (
  id INTEGER PRIMARY KEY,
  name text,
  address1 text,
  address2 text,
  city text,
  region text,
  country text,
  postalcode text,
  latitude float(8,2) DEFAULT NULL,
  longitude float(8,2) DEFAULT NULL,
  website text,
  phone text,
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT NULL
);

--
-- Table: lock_codes
--
CREATE TABLE lock_codes (
  id INTEGER PRIMARY KEY,
  uid tinytext,
  element_id int(11) DEFAULT NULL,
  claim_date int(11) DEFAULT NULL,
  creation_date int(11) DEFAULT 0,
  modification_date int(11) DEFAULT NULL
);

CREATE INDEX lock_codes_element_id ON lock_codes (element_id);

--
-- Table: lock_passwords
--
CREATE TABLE lock_passwords (
  id INTEGER PRIMARY KEY,
  password text,
  element_id int(11) DEFAULT NULL,
  creation_date int(11) DEFAULT 0,
  modification_date int(11) DEFAULT NULL
);

CREATE INDEX lock_passwords_element_id ON lock_passwords (element_id);

--
-- Table: asst_analytics
--
CREATE TABLE asst_analytics (
  id INTEGER PRIMARY KEY,
  asset_id int(11) DEFAULT 0,
  element_id int(11) DEFAULT NULL,
  access_time int(11),
  client_ip varchar(39),
  client_proxy varchar(39),
  cash_session_id varchar(24),
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT 0
);

CREATE INDEX asst_analytics_asset_id ON asst_analytics (id);

--
-- Table: lock_permissions
--
CREATE TABLE lock_permissions (
  id INTEGER PRIMARY KEY,
  user_id int(11),
  user_list_id int(11) DEFAULT 0,
  element_id int(11) DEFAULT 0,
  allowed_logins int(11) DEFAULT -1,
  total_logins int(11) DEFAULT 0,
  date_expires int(11) DEFAULT -1,
  element_password tinytext,
  added_by int(11) DEFAULT 0,
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT NULL
);

CREATE INDEX lock_permissions_login_id ON lock_permissions (user_list_id, element_id);

CREATE INDEX lock_permissions_element_id ON lock_permissions (element_id);

--
-- Table: user_users
--
CREATE TABLE user_users (
  id INTEGER PRIMARY KEY,
  email_address varchar(255) DEFAULT '',
  password char(64) DEFAULT '',
  username varchar(32) DEFAULT '',
  display_name tinytext,
  first_name tinytext,
  last_name tinytext,
  organization tinytext,
  address_line1 tinytext,
  address_line2 tinytext,
  address_city tinytext,
  address_region tinytext,
  address_postalcode tinytext,
  address_country tinytext,
  is_admin bool DEFAULT '0',
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT NULL
);

CREATE INDEX email_address ON user_users (email_address);

--
-- Table: user_lists
--
CREATE TABLE user_lists (
  id INTEGER PRIMARY KEY,
  name varchar(128) DEFAULT '',
  description text,
  user_id int(11),
  settings_id int(11),
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT 0
);

--
-- Table: user_resetpassword
--
CREATE TABLE user_resetpassword (
  id INTEGER PRIMARY KEY,
  time_requested int(11) DEFAULT 0,
  random_key tinytext,
  user_id int(11) DEFAULT 0,
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT NULL
);

--
-- Table: elmt_elements
--
CREATE TABLE elmt_elements (
  id INTEGER PRIMARY KEY,
  user_id int(11) DEFAULT NULL,
  name text,
  type text,
  options text,
  license_id int(11) DEFAULT 0,
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT NULL
);

--
-- Table: elmt_analytics
--
CREATE TABLE elmt_analytics (
  id INTEGER PRIMARY KEY,
  element_id int(11),
  access_method varchar(24),
  access_location text,
  access_action text,
  access_data text,
  access_time int(11),
  client_ip varchar(39),
  client_proxy varchar(39),
  cash_session_id varchar(24),
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT 0
);

CREATE INDEX elmt_analytics_element_id ON elmt_analytics (element_id);

--
-- Table: base_settings
--
CREATE TABLE base_settings (
  id INTEGER PRIMARY KEY,
  name text,
  type text,
  data text,
  user_id int(11),
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT NULL
);

--
-- Table: user_lists_members
--
CREATE TABLE user_lists_members (
  id INTEGER PRIMARY KEY,
  user_id int(11),
  list_id int(11),
  verification_code text,
  verified bit(1),
  initial_comment text,
  additional_data text,
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT 0
);

CREATE INDEX user_lists_members_user_id ON user_lists_members (user_id);

CREATE INDEX user_lists_members_list_id ON user_lists_members (list_id);

--
-- Table: live_guestlist
--
CREATE TABLE live_guestlist (
  id INTEGER PRIMARY KEY,
  event_id int(128),
  guest_name text,
  total_attendees int(11) DEFAULT 1,
  comment text,
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT 0
);

--
-- Table: base_metadata
--
CREATE TABLE base_metadata (
  id INTEGER PRIMARY KEY,
  scope_table_alias varchar(64) DEFAULT '',
  scope_table_id int(11) DEFAULT 0,
  user_id int(11) DEFAULT 0,
  type text,
  value text,
  creation_date int(11) DEFAULT NULL,
  modification_date int(11) DEFAULT NULL
);

CREATE INDEX base_metadata_scope_table ON base_metadata (scope_table_alias, scope_table_id);

COMMIT;
