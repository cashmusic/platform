<?php
/**
 * Data access for all Seed classes, DB and Session handling
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */abstract class CASHData {
	protected $db = false,$cash_session_timeout = 1800;

	/**
	 * 
	 * DATABASE CONNECTION
	 * Create and store new CASHDBA
	 *
	 */

	/**
	 * Grabs database connection properties from /settings/cashmusic.ini.php and
	 * opens the appropriate connection
	 *
	 * @return void
	 */protected function connectDB() {
		$cash_db_settings = parse_ini_file(CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php');
		require_once(CASH_PLATFORM_ROOT.'/classes/core/CASHDBA.php');
		$this->db = new CASHDBA(
			$cash_db_settings['hostname'],
			$cash_db_settings['username'],
			$cash_db_settings['password'],
			$cash_db_settings['database'],
			$cash_db_settings['driver']
		);
	}
	
	/**
	 * 
	 * SESSION HANDLERS
	 * Currently using standard $_SESSION calls, but may be wise to overwrite
	 * in favor of custom calls so we're not relying on over overwriting
	 * other sessions?
	 *
	 */

	/**
	 * Empties (or creates empty) entries to the standard $_SESSION array
	 *
	 * @return boolean
	 */protected function resetSession() {
		$_SESSION['cash_last_response'] = false;
		$_SESSION['cash_last_request_time'] = 9999999999;
		$_SESSION['cash_persistent_store'] = false;
		return true;
	}
	
	/**
	 * Sets the time against which the 
	 *
	 * @return boolean
	 */protected function startSession() {
		$this->cash_session_timeout = ini_get("session.gc_maxlifetime");
		if (isset($_SESSION['cash_last_request_time'])) {
			if ($_SESSION['cash_last_request_time'] + $this->cash_session_timeout < time()) {
				$this->resetSession();
			}
		}
		$_SESSION['cash_last_request_time'] = time();
		return true;
	}

	/**
	 * Replaces $_SESSION['cash_last_response'] with a new response
	 *
	 * @param {array} $response - the new CASHResponse
	 * @param {boolean} $reset_session_id [default: false] - if true a new 
	 *        session id is generated as a security measure 
	 * @return boolean
	 */protected function sessionSetLastResponse($response,$reset_session_id=false) {
		if (!isset($_SESSION['cash_last_response'])) {
			$this->resetSession();
		}
		if ($reset_session_id) {
			session_regenerate_id(true);
		}
		$_SESSION['cash_last_response'] = $response;
		return true;
	}

	/**
	 * Returns the current value of $_SESSION['cash_last_response']
	 *
	 * @return array|false
	 */public function sessionGetLastResponse() {
		if (!isset($_SESSION['cash_last_response'])) {
			$this->resetSession();
		}
		return $_SESSION['cash_last_response'];
	}

	/**
	 * Sets $_SESSION['cash_last_response'] to false
	 *
	 * @return array|false
	 */public function sessionClearLastResponse() {
		$_SESSION['cash_last_response'] = false;
		return true;
	}

	/**
	 * Adds new data to the $_SESSION['cash_persistent_store'] array and resets
	 * the session id as a security precaution. 
	 *
	 * @param {string} $key - the key to associate with the new data
	 * @param {*} $value - the data to store
	 * @return boolean
	 */public function sessionSetPersistent($key,$value) {
		if (!isset($_SESSION['cash_persistent_store'])) {
			$this->resetSession();
		}
		if (is_array($_SESSION['cash_persistent_store'])) {
			$_SESSION['cash_persistent_store'][(string)$key] = $value;
		} else {
			$_SESSION['cash_persistent_store'] = array((string)$key => $value);
		}
		$_SESSION['cash_persistent_store']['session_regenerate_id'] = true;
		return true;
	}

	/**
	 * Returns data from the $_SESSION['cash_persistent_store'] array
	 *
	 * @param {string} $key - the key associated with the requested data
	 * @return *|false
	 */public function sessionGetPersistent($key) {
		if (!isset($_SESSION['cash_persistent_store'])) {
			$this->resetSession();
			return false;
		} 
		if (isset($_SESSION['cash_persistent_store'][(string)$key])) {
			return $_SESSION['cash_persistent_store'][(string)$key];
		} else {
			return false;
		}
	}

	/**
	 * Removes the key/value entry for a specified key
	 *
	 * @param {string} $key - the key to be removed
	 * @return void
	 */public function sessionClearPersistent($key) {
		if (!isset($_SESSION['cash_persistent_store'])) {
			$this->resetSession();
		} else if (isset($_SESSION['cash_persistent_store'][(string)$key])) {
			unset($_SESSION['cash_persistent_store'][(string)$key]);
		} 
	}

	/**
	 * Removes all data from $_SESSION['cash_persistent_store'], setting it false
	 *
	 * @return void
	 */public function sessionClearAllPersistent() {
		$_SESSION['cash_persistent_store'] = false;
	}
	
	/**
	 * 
	 * METADATA
	 * Metadata can be applied to any table by way of a scope table (alias) and
	 * id. These functions make access available to all plants.
	 *
	 */

	public function setMetaData($scope_table_alias,$scope_table_id,$user_id,$data_key,$data_value) {
		// try to find an exact key/value match
		$selected_tag = $this->getMetaData($scope_table_alias,$scope_table_id,$user_id,$data_key,$data_value);
		if (!$selected_tag) {
			$data_key_exists = $this->getMetaData($scope_table_alias,$scope_table_id,$user_id,$data_key);
			if ($data_key == 'tag' || !$data_key_exists) {
				// no matching tag or key, so we can just create a new one
				$result = $this->db->setData(
					'metadata',
					array(
						'scope_table_alias' => $scope_table_alias,
						'scope_table_id' => $scope_table_id,
						'type' => $data_key,
						'value' => $data_value,
						'user_id' => $user_id
					)
				);
			} else {
				// key already exists and isn't a tag, so we need to edit the value
				$result = $this->db->setData(
					'metadata',
					array(
						'value' => $data_value
					),
					array(
						'id' => array(
							'condition' => '=',
							'value' => $data_key_exists['id']
						)
					)
				);
			}
			return $result;
		} else {
			// exact match: metadata exists as requested. return true
			return true;
		}
	}
	
	public function getMetaData($scope_table_alias,$scope_table_id,$user_id,$data_key,$data_value=false) {
		// set up options for the query. leave off $data_value to widen the results
		// by default
		$options_array = array(
			"scope_table_alias" => array(
				"condition" => "=",
				"value" => $scope_table_alias
			),
			"scope_table_id" => array(
				"condition" => "=",
				"value" => $scope_table_id
			),
			"type" => array(
				"condition" => "=",
				"value" => $data_key
			),
			"user_id" => array(
				"condition" => "=",
				"value" => $user_id
			)
		);
		// if $data_value is set, add it to the options for refined search (tags)
		if ($data_value) {
			$options_array['value'] = array(
				"condition" => "=",
				"value" => $data_value
			);
		}
		// do the query
		$result = $this->db->getData(
			'metadata',
			'*',
			$options_array
		);
		if ($result) {
			if ($data_value && $data_key != 'tag') {
				// $data_value means a unique set, give direct access to array
				return $result[0];
			} else {
				// without $data_value set there could be multiple results (tags only)
				return $result;
			}
		} else {
			return false;
		}
	}

	public function removeMetaData($metadata_id) {
		$result = $this->db->deleteData(
			'metadata',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $metadata_id
				)
			)
		);
		return $result;
	}
	
	public function removeAllMetaDataForItem($scope_table_alias,$scope_table_id,$user_id=false) {
		// set table / id up front. if no user is specified it will remove ALL
		// metadata for a given table+id — used primarily when deleting the parent item
		$conditions_array = array(
			'scope_table_alias' => array(
				'condition' => '=',
				'value' => $scope_table_alias
			),
			'scope_table_id' => array(
				'condition' => '=',
				'value' => $scope_table_id
			)
		);
		if ($user_id) {
			// if a $user_id is present refine the search
			$conditions_array['user_id'] = array(
				'condition' => '=',
				'value' => $user_id
			);
		}
		$result = $this->db->deleteData(
			'metadata',
			$conditions_array
		);
		return $result;
	}

	public function getAllMetaData($scope_table_alias,$scope_table_id,$user_id,$ignore_or_match='match',$data_key=false) {
		$options_array = array(
			"scope_table_alias" => array(
				"condition" => "=",
				"value" => $scope_table_alias
			),
			"scope_table_id" => array(
				"condition" => "=",
				"value" => $scope_table_id
			),
			"user_id" => array(
				"condition" => "=",
				"value" => $user_id
			)
		);
		// most $data_keys will be unique per user per table+id, but tags need multiple
		// so we'll add a filter. pass 'tag' as the final option to getAllMetaData
		// to get an array of all tag rows for a single table+id
		$key_condition = "=";
		if ($ignore_or_match = 'ignore') {
			$key_condition = "!=";
		}
		if ($data_key) {
			$options_array['type'] = array(
				"condition" => $key_condition,
				"value" => $data_key
			);
		}
		$result = $this->db->getData(
			'metadata',
			'*',
			$options_array
		);
		if ($result) {
			$return_array = array();
			foreach ($result as $row) {
				if ($data_key == 'tag' && $ignore_or_match == 'match') {
					$return_array[] = $row['value'];
				} else {
					$return_array[$row['type']] = $row['value'];
				}
			}
		} else {
			return false;
		}
	}
	
	public function batchSetTags($scope_table_alias,$scope_table_id,$user_id,$tags) {
		foreach ($tags as $tag) {
			$this->setMetaData($scope_table_alias,$scope_table_id,$user_id,'tag',$tag);
		}
	}
	
	public function batchSetMetaData($scope_table_alias,$scope_table_id,$user_id,$metadata) {
		foreach ($metadata as $key => $value) {
			$this->setMetaData($scope_table_alias,$scope_table_id,$user_id,$key,$value);
		}
	}
	
} // END class 
?>