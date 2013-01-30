<?php
/**
 * CASHConnection stores and retrieves 3rd party API connection settings from the 
 * database. API settings definitions are stored as JSON flat files in /settings/connections 
 * then read in by this class. Actual API keys and needed settings are stored as JSON 
 * in the settings table in the database.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 * This file is generously sponsored by Anchor Brain
 * Anchor Brain: A Providence based record label featuring releases by bands like 
 * Doomsday Student, What Cheer? Brigade, Six Finger Satellite. Website: anchorbrain.com
 *
 */class CASHConnection extends CASHData {
	public $user_id,$connection_id,$connection_name,$creation_date;
	
	public function __construct($user_id=false,$connection_id=false) {
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
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
	 */public function getConnectionTypes($filter_by_scope=false) {
		if ($settings_dir = opendir(CASH_PLATFORM_ROOT.'/settings/connections')) {
			$settings_types = false;
			while (false !== ($file = readdir($settings_dir))) {
				if (substr($file,0,1) != "." && !is_dir($file)) {
					$tmp_key = strtolower(substr_replace($file, '', -5));
					$tmp_value = json_decode(file_get_contents(CASH_PLATFORM_ROOT.'/settings/connections/'.$file),true);
					if ($filter_by_scope) {
						if (!in_array($filter_by_scope, $tmp_value['scope'])) {
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
	 */public function getAllConnectionsforUser() {
		if ($this->user_id) {
			$result = $this->db->getData(
				'connections',
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
	 * Returns the decoded JSON for the setting id the CASHConnection
	 * object was instantiated with. 
	 *
	 * @return settings obj
	 */public function getConnectionSettings($id_override=false) {
		if (!$id_override) {
			$connection_id = $this->connection_id;
		} else {
			$connection_id = $id_override;
		}
		if ($connection_id) {
			$result = $this->db->getData(
				'connections',
				'name,data,creation_date',
				array(
					"id" => array(
						"condition" => "=",
						"value" => $connection_id
					),
					"user_id" => array(
						"condition" => "=",
						"value" => $this->user_id
					)
				)
			);
			if ($result) {
				$this->settings = json_decode(CASHSystem::simpleXOR(base64_decode($result[0]['data'])),true);
				$this->connection_name = $result[0]['name'];
				$this->creation_date = $result[0]['creation_date'];;
				return $this->settings;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 *
	 * @return settings obj
	 */public function getConnectionsByType($settings_type) {
		$result = $this->db->getData(
			'connections',
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
	 * Returns the decoded JSON for the specified connection scope
	 *
	 * @return settings obj
	 */public function getConnectionsByScope($scope) {
		$connection_types_data = $this->getConnectionTypes($scope);
		$applicable_settings_array = false;
		$all_connections = $this->getAllConnectionsforUser();
		$filtered_connections = array();
		
		if (is_array($all_connections)) {
			foreach ($all_connections as $key => $data) {
				if (array_key_exists($data['type'],$connection_types_data)) {
					$filtered_connections[] = $data;
				}
			}
		}
		
		if (count($filtered_connections)) {
			foreach ($filtered_connections as &$connection) {
				$connection['data'] = json_decode(CASHSystem::simpleXOR(base64_decode($connection['data'])),true);
			}
			return $filtered_connections;
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
		if (isset($this->settings[(string)$setting_name])) {
			return $this->settings[(string)$setting_name];
		} else {
			return false;
		}
	}

	/**
	 * 
	 *
	 * @param {array} settings_data: settings data as associative array
	 * @return boolean
	 */public function setSettings($settings_name,$settings_type,$settings_data,$connection_id=false) {
		$settings_data = json_encode($settings_data);
		if ($connection_id) {
			$settings_condition = array(
				'id' => array(
					'condition' => '=',
					'value' => $connection_id
				)
			);
			$allow_action = true;
		} else {
			$settings_condition = false;
			$allow_action = $this->checkUniqueName($settings_name,$settings_type);
		}
		if ($allow_action) {
			$result = $this->db->setData(
				'connections',
				array(
					'name' => $settings_name,
					'type' => $settings_type,
					'user_id' => $this->user_id,
					'data' => base64_encode(CASHSystem::simpleXOR($settings_data))
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
	 * @param {array} settings_data: settings data as associative array
	 * @return boolean
	 */public function updateSettings($settings_data) {
		$settings_data = json_encode($settings_data);
		$result = $this->db->setData(
			'connections',
			array(
				'data' => base64_encode(CASHSystem::simpleXOR($settings_data))
			),
			array(
				'id' => array(
					'condition' => '=',
					'value' => $this->connection_id
				)
			)
		);
		return $result;
	}

	/**
	 * 
	 *
	 * @param {int} connection_id
	 * @return boolean
	 */public function deleteSettings($connection_id) {
		$result = $this->db->deleteData(
			'connections',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $connection_id
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
			'connections',
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