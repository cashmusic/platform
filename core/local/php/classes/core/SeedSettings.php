<?php
/**
 * Store and retrieve settings, designed to add/get JSON data from DB
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class SeedSettings extends SeedData {
	public function __construct($settings_type,$settings_name='default') {
		$this->settings_name = $settings_name;
		$this->settings_type = $settings_type;
		$this->settings_name_escaped = "'" . mysql_real_escape_string($this->settings_name) . "'";
		$this->settings_type_escaped = "'" . mysql_real_escape_string($this->settings_type) . "'";
		$this->settings = null;
		$this->connectDB();
	}
	
	public function getSettings() {
		if ($this->settings_name == 'default') {
			$query = "SELECT data FROM seed_settings WHERE type = {$this->settings_type_escaped} AND isdefault = 1";
		} else {
			$query = "SELECT data FROM seed_settings WHERE name = {$this->settings_name_escaped} AND type = {$this->settings_type_escaped}";
		}
		$result = $this->db->doQueryForAssoc($query);
		if ($result) {
			$this->settings = json_decode($result['data']);
			return $this->settings;
		} else {
			return false;
		}
	}

	public function getSetting($setting_name) {
		if (isset($this->settings->$setting_name)) {
			return $this->settings->$setting_name;
		} else {
			return false;
		}
	}

	public function addSettings($settings_data,$set_default=false) {
		$settings_data = json_encode($settings_data);
		$settings_data = "'" . mysql_real_escape_string($settings_data) . "'";
		if ($this->checkUniqueName() && $this->settings_name != 'default') {
			$current_date = time();
			if ($set_default) {
				$query = "UPDATE seed_settings SET isdefault=false,modification_date=$current_date WHERE type = {$this->settings_type_escaped}";
				$success = $this->db->doQuery($query);
				if (!$success) {
					// error: could not reset defaults in existing settings
					return false;
				}
				$set_default = 'true';
			} else {
				if ($this->checkFirstByType()) {
					$set_default = 'true';
				} else {
					$set_default = 'false';
				}
			}
			$query = "INSERT INTO seed_settings (name,type,data,isdefault,creation_date) VALUES ({$this->settings_name_escaped},{$this->settings_type_escaped},$settings_data,$set_default,$current_date)";
			if ($this->db->doQuery($query)) { 
				return true;
			} else {
				// error inserting settings
				return false;
			}
		} else {
			// error: you must specify unique a name when adding settings
			return false;
		}
	}
	
	private function checkUniqueName() {
		$query = "SELECT name FROM seed_settings WHERE name = {$this->settings_name_escaped} AND type = {$this->settings_type_escaped}";
		$count = $this->db->doQueryForCount($query);
		if ($count) {
			return false;
		} else {
			return true;
		}
	}
	
	private function checkFirstByType() {
		$query = "SELECT name FROM seed_settings WHERE type = {$this->settings_type_escaped}";
		$count = $this->db->doQueryForCount($query);
		if ($count) {
			return false;
		} else {
			return true;
		}
	}
} // END class 
?>