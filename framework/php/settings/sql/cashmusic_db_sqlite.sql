-- 
-- CASH Music distributed platform
-- flavor: SQLite
-- schema version: 1
-- modified: January 13, 2012

BEGIN TRANSACTION;

-- 
-- 
-- Section: ASSETS
-- 
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

CREATE TABLE assets_analytics (
  id INTEGER PRIMARY KEY,
  asset_id integer DEFAULT '0',
  element_id integer DEFAULT NULL,
  access_time integer,
  client_ip text,
  client_proxy text,
  cash_session_id text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);
CREATE INDEX assets_analytics_asset_id ON assets_analytics (id);


-- 
-- 
-- Section: CALENDAR
-- 
CREATE TABLE calendar_events (
  id INTEGER PRIMARY KEY,
  date integer DEFAULT NULL,
  user_id integer DEFAULT NULL,
  venue_id integer DEFAULT NULL,
  published integer DEFAULT NULL,
  cancelled integer DEFAULT NULL,
  purchase_url text,
  comments text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);
CREATE INDEX calendar_events_user_id ON calendar_events (user_id);

CREATE TABLE calendar_venues (
  id INTEGER PRIMARY KEY,
  name text,
  address1 text,
  address2 text,
  city text,
  region text,
  country text,
  postalcode text,
  latitude numeric DEFAULT NULL,
  longitude numeric DEFAULT NULL,
  url text,
  phone text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

CREATE TABLE calendar_guestlist (
  id INTEGER PRIMARY KEY,
  event_id integer,
  guest_name text,
  total_attendees integer DEFAULT '1',
  comment text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);


-- 
-- 
-- Section: COMMERCE
-- 
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

CREATE TABLE commerce_offers_included_items (
  id integer PRIMARY KEY,
  offer_id integer,
  item_id integer DEFAULT NULL,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

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


-- 
-- 
-- Section: ELEMENTS
-- 
CREATE TABLE elements (
  id INTEGER PRIMARY KEY,
  user_id integer DEFAULT NULL,
  name text,
  type text,
  options text,
  license_id integer DEFAULT '0',
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

CREATE TABLE elements_analytics (
  id INTEGER PRIMARY KEY,
  element_id integer,
  access_method text,
  access_location text,
  access_action text,
  access_data text,
  access_time integer,
  client_ip text,
  client_proxy text,
  cash_session_id text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);
CREATE INDEX elements_analytics_element_id ON elements_analytics (element_id);


-- 
-- 
-- Section: PEOPLE
-- 
CREATE TABLE people (
  id INTEGER PRIMARY KEY,
  email_address text DEFAULT '',
  password text DEFAULT '',
  username text DEFAULT '',
  display_name text,
  first_name text,
  last_name text,
  organization text,
  address_line1 text,
  address_line2 text,
  address_city text,
  address_region text,
  address_postalcode text,
  address_country text,
  is_admin integer DEFAULT '0',
  api_key text DEFAULT '',
  api_secret text DEFAULT '',
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);
CREATE INDEX email ON people (email_address);

CREATE TABLE people_analytics (
  id INTEGER PRIMARY KEY,
  user_id integer DEFAULT '0',
  element_id integer DEFAULT NULL,
  access_time integer,
  client_ip text,
  client_proxy text,
  login_method text DEFAULT NULL,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);

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
  notes text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

CREATE TABLE people_lists (
  id INTEGER PRIMARY KEY,
  name text DEFAULT '',
  description text,
  user_id integer,
  connection_id integer,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);

CREATE TABLE people_lists_members (
  id INTEGER PRIMARY KEY,
  user_id integer,
  list_id integer,
  verification_code text,
  verified integer DEFAULT '0',
  active integer DEFAULT '1',
  initial_comment text,
  additional_data text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT '0'
);
CREATE INDEX people_lists_members_user_id ON people_lists_members (user_id);
CREATE INDEX people_lists_members_list_id ON people_lists_members (list_id);

CREATE TABLE people_resetpassword (
  id INTEGER PRIMARY KEY,
  time_requested integer DEFAULT '0',
  random_key text,
  user_id integer DEFAULT '0',
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);


-- 
-- 
-- Section: SYSTEM
-- 
CREATE TABLE system_analytics (
  id INTEGER PRIMARY KEY,
  type text,
  filter text,
  primary_value text,
  details text,
  user_id integer,
  scope_table_alias text DEFAULT NULL,
  scope_table_id integer DEFAULT NULL,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

CREATE TABLE system_connections (
  id INTEGER PRIMARY KEY,
  name text,
  type text,
  data text,
  user_id integer,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

CREATE TABLE system_licenses (
  id INTEGER PRIMARY KEY,
  name text,
  description text,
  fulltext blob,
  url text
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

CREATE TABLE system_lock_codes (
  id INTEGER PRIMARY KEY,
  uid text,
  element_id integer DEFAULT NULL,
  claim_date integer DEFAULT NULL,
  creation_date integer DEFAULT '0',
  modification_date integer DEFAULT NULL
);
CREATE INDEX system_lock_codes_element_id ON system_lock_codes (element_id);

CREATE TABLE system_metadata (
  id INTEGER PRIMARY KEY,
  scope_table_alias text DEFAULT '',
  scope_table_id integer DEFAULT '0',
  user_id integer DEFAULT '0',
  type text,
  value text,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);
CREATE INDEX system_metadata_scope_table ON system_metadata (scope_table_alias, scope_table_id);

CREATE TABLE system_settings (
  id INTEGER PRIMARY KEY,
  type text,
  value text,
  user_id integer,
  creation_date integer DEFAULT NULL,
  modification_date integer DEFAULT NULL
);

COMMIT;
