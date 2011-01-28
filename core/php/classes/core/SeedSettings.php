<?php
/**
 * Store and retrieve settings, designed to add/get JSON data from DB
 *
 * @package seed.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */class SeedSettings extends SeedData {
	
	public function __construct($settings_type,$settings_name='default') {
		$this->settings_name = $settings_name;
		$this->settings_type = $settings_type;
		$this->settings = null;
		$this->connectDB();
	}
	
	public function getSettings() {
		if ($this->settings_name == 'default') {
			$result = $this->db->getData(
				'seed_settings',
				'data',
				"type = '{$this->settings_type}' AND isdefault = 1"
			);
		} else {
			$result = $this->db->getData(
				'seed_settings',
				'data',
				"name = '{$this->settings_name}' AND type = '{$this->settings_type}'"
			);
		}
		if ($result) {
			$this->settings = json_decode($result[0]['data']);
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
		if ($this->checkUniqueName() && $this->settings_name != 'default') {
			$current_date = time();
			if ($set_default) {
				$result = $this->db->setData(
					'seed_settings',
					array(
						'name' => $this->settings_name,
						'type' => $this->settings_type,
						'data' => $settings_data,
						'isdefault' => false
					),
					"type = '{$this->settings_type}'"
				);
				if (!$result) {
					// error: could not reset defaults in existing settings
					return false;
				}
			}
			$result = $this->db->setData(
				'seed_settings',
				array(
					'name' => $this->settings_name,
					'type' => $this->settings_type,
					'data' => $settings_data,
					'isdefault' => $set_default
				)
			);
			return $result;
		} else {
			// error: you must specify unique a name when adding settings
			return false;
		}
	}
	
	private function checkUniqueName() {
		$result = $this->db->getData(
			'seed_settings',
			'name',
			"name = '{$this->settings_name}' AND type = '{$this->settings_type}'"
		);
		return $result;
	}
	
	private function checkFirstByType() {
		$result = $this->db->getData(
			'seed_settings',
			'name',
			"name = 'type = '{$this->settings_type}'"
		);
		return $result;
	}
} // END class 
?>