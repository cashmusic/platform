<?php
/**
 * Store and retrieve settings, designed to add/get JSON data from DB
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */class CASHSettings extends CASHData {
	
	public function __construct($user_id=false,$settings_id=false) {
		$this->user_id = $user_id;
		$this->settings_id = $settings_id;
		$this->settings = null;
		$this->connectDB();
	}
	
	/**
	 * 
	 * PLATFORM / GENERAL USER SETTINGS
	 * These functions don't handle specific settings, rather find what's available
	 * on a platform level, find all settings for a given user, etc.
	 *
	 */
	
	/**
	 * Finds all settings type JSON files, builds an array keyed by type
	 *
	 * @return array
	 */public function getSettingsTypes($filter_by_scope=false) {
		if ($settings_dir = opendir(CASH_PLATFORM_ROOT.'/settings/types')) {
			$settings_types = false;
			while (false !== ($file = readdir($settings_dir))) {
				if (substr($file,0,1) != "." && !is_dir($file)) {
					$tmp_key = strtolower(substr_replace($file, '', -5));
					$tmp_value = json_decode(file_get_contents(CASH_PLATFORM_ROOT.'/settings/types/'.$file));
					if ($filter_by_scope) {
						if (!in_array($filter_by_scope, $tmp_value->scope)) {
							$tmp_value = false;
						}
					}
					if ($tmp_value !== false) {
						if (!$settings_types) { $settings_types = array(); }
						$settings_types["$tmp_key"] = $tmp_value;
					}
				}
			}
			closedir($settings_dir);
			return $settings_types;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns all settings for a given user
	 *
	 * @return array
	 */public function getAllSettingsforUser() {
		if ($this->user_id) {
			$result = $this->db->getData(
				'settings',
				'*',
				array(
					"user_id" => array(
						"condition" => "=",
						"value" => $this->user_id
					)
				)
			);
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 
	 * SPECIFIC SESSION FUNCTIONS
	 * These return or set individual settings
	 *
	 */
	
	/**
	 * Returns the decoded JSON for the setting id the CASHSettings
	 * object was instantiated with. 
	 *
	 * @return settings obj
	 */public function getSettings($id_override=false) {
		if (!$id_override) {
			$settings_id = $this->settings_id;
		} else {
			$settings_id = $id_override;
		}
		if ($settings_id) {
			$result = $this->db->getData(
				'settings',
				'data',
				array(
					"id" => array(
						"condition" => "=",
						"value" => $settings_id
					),
					"user_id" => array(
						"condition" => "=",
						"value" => $this->user_id
					)
				)
			);
			if ($result) {
				$this->settings = json_decode($result[0]['data']);
				return $this->settings;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the decoded JSON for the setting type/name the CASHSettings
	 * object was instantiated with. 
	 *
	 * @return settings obj
	 */public function getSettingsByType($settings_type) {
		$result = $this->db->getData(
			'settings',
			'*',
			array(
				"type" => array(
					"condition" => "=",
					"value" => $settings_type
				),
				"user_id" => array(
					"condition" => "=",
					"value" => $this->user_id
				)
			)
		);
		return $result;
	}

	/**
	 * Returns the specific setting
	 *
	 * @param {string} settings name
	 * @return settings obj
	 */public function getSetting($setting_name) {
		if (isset($this->settings->$setting_name)) {
			return $this->settings->$setting_name;
		} else {
			return false;
		}
	}

	/**
	 * 
	 *
	 * @param {array} settings_data: settings data as associative array
	 * @return boolean
	 */public function setSettings($settings_name,$settings_type,$settings_data,$settings_id=false) {
		$settings_data = json_encode($settings_data);
		if ($settings_id) {
			$settings_condition = array(
				'id' => array(
					'condition' => '=',
					'value' => $settings_id
				)
			);
			$allow_action = true;
		} else {
			$settings_condition = false;
			$allow_action = $this->checkUniqueName($settings_name,$settings_type);
		}
		if ($allow_action) {
			$result = $this->db->setData(
				'settings',
				array(
					'name' => $settings_name,
					'type' => $settings_type,
					'user_id' => $this->user_id,
					'data' => $settings_data
				),
				$settings_condition
			);
			return $result;
		} else {
			// error: you must specify unique a name when adding settings
			return false;
		}
	}

	/**
	 * 
	 *
	 * @param {int} settings_id
	 * @return boolean
	 */public function deleteSettings($settings_id) {
		$result = $this->db->deleteData(
			'settings',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $settings_id
				)
			)
		);
		return $result;
	}	

	/**
	 * Ensures that the specified name / type combination is unique per user
	 *
	 * @return boolean
	 */private function checkUniqueName($settings_name,$settings_type) {
		$result = $this->db->getData(
			'settings',
			'name',
			array(
				'type' => array(
					'condition' => '=',
					'value' => $settings_type
				),
				'name' => array(
					'condition' => '=',
					'value' => $settings_name
				),
				'user_id' => array(
					'condition' => '=',
					'value' => $this->user_id
				)
			)
		);
		if ($result) {
			return false;
		} else {
			return true;
		}
	}
} // END class 
?>