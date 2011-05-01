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
	
	public function __construct($settings_type,$user_id,$settings_name='default') {
		$this->settings_name = $settings_name;
		$this->settings_type = $settings_type;
		$this->user_id = $user_id;
		$this->settings = null;
		$this->connectDB();
	}
	
	/**
	 * Returns the decoded JSON for the setting type/name the CASHSettings
	 * object was instantiated with. 
	 *
	 * @return settings obj
	 */public function getSettings() {
		if ($this->settings_name == 'default') {
			$result = $this->db->getData(
				'settings',
				'data',
				array(
					"type" => array(
						"condition" => "=",
						"value" => $this->settings_type
					),
					"isdefault" => array(
						"condition" => "=",
						"value" => 1
					),
					"user_id" => array(
						"condition" => "=",
						"value" => $this->user_id
					)
				)
			);
		} else {
			$result = $this->db->getData(
				'settings',
				'data',
				array(
					"type" => array(
						"condition" => "=",
						"value" => $this->settings_type
					),
					"name" => array(
						"condition" => "=",
						"value" => $this->settings_name
					),
					"user_id" => array(
						"condition" => "=",
						"value" => $this->user_id
					)
				)
			);
		}
		if ($result) {
			$this->settings = json_decode($result[0]['data']);
			return $this->settings;
		} else {
			return false;
		}
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
	 * @param {boolean} set_default: whether or not these settings should be default for this type
	 * @return boolean
	 */public function addSettings($settings_data,$set_default=false) {
		$settings_data = json_encode($settings_data);
		if ($this->checkUniqueName() && $this->settings_name != 'default') {
			$current_date = time();
			if ($set_default) {
				$result = $this->db->setData(
					'settings',
					array(
						'name' => $this->settings_name,
						'type' => $this->settings_type,
						'user_id' => $this->user_id,
						'data' => $settings_data,
						'isdefault' => false
					),
					array(
						'type' => array(
							'condition' => '=',
							'value' => $this->settings_type
						)
					)
				);
				if (!$result) {
					// error: could not reset defaults in existing settings
					return false;
				}
			}
			$result = $this->db->setData(
				'settings',
				array(
					'name' => $this->settings_name,
					'type' => $this->settings_type,
					'user_id' => $this->user_id,
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
	
	/**
	 * Ensures that the specified name / type combination is unique per user
	 *
	 * @return boolean
	 */private function checkUniqueName() {
		$result = $this->db->getData(
			'settings',
			'name',
			array(
				'type' => array(
					'condition' => '=',
					'value' => $this->settings_type
				),
				'name' => array(
					'condition' => '=',
					'value' => $this->settings_name
				),
				'user_id' => array(
					'condition' => '=',
					'value' => $this->user_id
				)
			)
		);
		return $result;
	}
} // END class 
?>