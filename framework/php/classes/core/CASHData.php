<?php
/**
 * Data access for all Plant and Seed classes. CASHData abstracts out SESSION 
 * data handling, provides a CASHDBA object as $this->db, and provides functions 
 * to access metadata for all tables. 
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
	protected $db = false,
			  $cash_session_timeout = 1800,
			  $cache_enabled = false,
			  $cache_dir = null;

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
	 * Sets the time against which the session is measured. This function also
	 * sets the cash_session_id internally as a mechanism for tracking analytics
	 * against a consistent id, regardless of PHP session id.
	 *
	 * @return boolean
	 */protected function startSession() {
		// begin PHP session
		if(!defined('STDIN')) { // no session for CLI, suckers
			@session_cache_limiter('nocache');
			$session_length = 3600;
			@ini_set("session.gc_maxlifetime", $session_length); 
			@session_start();
		}
		
		$this->cash_session_timeout = ini_get("session.gc_maxlifetime");
		if (!isset($_SESSION['cash_session_id'])) {
			$modifier_array = array('deedee','johnny','joey','tommy','marky');
			$_SESSION['cash_session_id'] = $modifier_array[array_rand($modifier_array)] . '_' . rand(1000,9999) . substr((string)time(),4);
		}
		if (isset($_SESSION['cash_last_request_time'])) {
			if ($_SESSION['cash_last_request_time'] + $this->cash_session_timeout < time()) {
				$this->resetSession();
			}
		}
		$_SESSION['cash_last_request_time'] = time();
		return true;
	}
	
	/**
	 * Returns the internal cash_session_id (Not the PHP session id)
	 *
	 * @return boolean
	 */protected function getCASHSessionID() {
		return $_SESSION['cash_session_id'];
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
		if ($reset_session_id && !defined('STDIN')) {
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
		if(!defined('STDIN')) {
			session_regenerate_id(true);
		}
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
	
	public function removeAllMetaData($scope_table_alias,$scope_table_id,$user_id=false,$ignore_or_match='match',$data_key=false) {
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
		if ($data_key) {
			$key_condition = "=";
			if ($ignore_or_match = 'ignore') {
				$key_condition = "!=";
			}
			$options_array['type'] = array(
				"condition" => $key_condition,
				"value" => $data_key
			);
		}
		$result = $this->db->deleteData(
			'metadata',
			$conditions_array
		);
		return $result;
	}

	public function getAllMetaData($scope_table_alias,$scope_table_id,$data_key=false,$ignore_or_match='match') {
		$options_array = array(
			"scope_table_alias" => array(
				"condition" => "=",
				"value" => $scope_table_alias
			),
			"scope_table_id" => array(
				"condition" => "=",
				"value" => $scope_table_id
			)
		);
		// most $data_keys will be unique per user per table+id, but tags need multiple
		// so we'll add a filter. pass 'tag' as the final option to getAllMetaData
		// to get an array of all tag rows for a single table+id
		if ($data_key) {
			$key_condition = "=";
			if ($ignore_or_match == 'ignore') {
				$key_condition = "!=";
			}
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
					if ($row['type'] !== 'tag') {
						$return_array[$row['type']] = $row['value'];
					}
				}
			}
			return $return_array;
		} else {
			return false;
		}
	}
	
	public function setAllMetaData($scope_table_alias,$scope_table_id,$user_id,$tags=false,$metadata=false,$delete_existing=false) {
		// also need to add $ignore_or_match='match',$data_key=false to removeAllMetaData
		if ($tags) {
			// first get current tags and remove any that are no longer in the list
			$current_tags = $this->getAllMetaData($scope_table_alias,$scope_table_id,$user_id,'match','tag');
			if ($current_tags) {
				foreach ($current_tags as $tag) {
					if (!in_array($tag, $tags)) {
						$tag_details = $this->getMetaData($scope_table_alias,$scope_table_id,$user_id,'tag',$tag);
						$tag_id = $tag_details[0]['id'];
						$this->removeMetaData($tag_id);
					}
				}
			}
			// run setMetaData on all passed tags - will edit existing tags and add new ones
			foreach ($tags as $tag) {
				$this->setMetaData($scope_table_alias,$scope_table_id,$user_id,'tag',$tag);
			}
		} else {
			// remove all tags if delete_existing is set
			if ($delete_existing) {
				$this->removeAllMetaData($scope_table_alias,$scope_table_id,$user_id,'match','tag');
			}
		}
		if ($metadata) {
			$current_metadata = $this->getAllMetaData($scope_table_alias,$scope_table_id,$user_id,'ignore','tag');
			if ($current_metadata) {
				foreach ($current_metadata as $key => $value) {
					if (!array_key_exists($key, $metadata)) {
						$metadata_details = $this->getMetaData($scope_table_alias,$scope_table_id,$user_id,$key,$value);
						$metadata_id = $metadata_details['id'];
						$this->removeMetaData($tag_id);
					}
				}
			}
			foreach ($metadata as $key => $value) {
				$this->setMetaData($scope_table_alias,$scope_table_id,$user_id,$key,$value);
			}
		} else {
			if ($delete_existing) {
				// remove all non-tag metadata if delete_existing is set
				$this->removeAllMetaData($scope_table_alias,$scope_table_id,$user_id,'ignore','tag');
			}
		}
	}

	/**
	 *
	 * FEED/DATA CACHE STUFF
	 * Functions to read and write data to file — useful both for raw data and 
	 * structured JSON. Primarily used for feeds from API scrapes, etc.
	 *
	 */

	/**
	 * Readies the basic file cache for JSON/feed caching — essentially just tests 
	 * to ensure that the cache directory exists and is writeable. primeCache() will 
	 * set $this->cache_enabled true on success.
	 *
	 * @return void
	 */protected function primeCache($cache_dir=false) {
		if (!$cache_dir) {
			$cache_dir = CASH_PLATFORM_ROOT.'/../cache';
		}
		if (file_exists($cache_dir)) {
			$this->cache_dir = $cache_dir;
			if (is_writable($cache_dir) && is_readable($cache_dir)) {
				$this->cache_enabled = true;
			}
		} else {
			if (mkdir($cache_dir)) {
				$this->cache_dir = $cache_dir;
				$this->cache_enabled = true;
			}
		}
	}

	/**
	 * Sets the contents of a given cache file. Setting $encode will tell it to 
	 * encode the data as JSON or not.
	 *
	 * @return string or decoded JSON object/array
	 */public function setCacheData($cache_name, $data_name, $data, $encode=true) {
		if ($this->cache_enabled) {
			if ($encode) {
				$payload = json_encode($data);
				$file_extension = '.json';
			} else {
				$payload = $data;
				$file_extension = '.utf8';
			}
			$datafile = $this->cache_dir . '/' . $cache_name . '/' . $data_name . $file_extension;
			if (!file_exists($this->cache_dir . '/' . $cache_name)) {
				mkdir($this->cache_dir . '/' . $cache_name, 0777, true);
			}
			$success = file_put_contents($datafile, $payload);
			return $success;
		} else {
			return false;
		}
	}
	
	/**
	 * Gets the contents of a given cache file. If $force_last is set it will 
	 * ignore expiry state and simply return the data in the file regardless. 
	 * Setting $decode will tell it to parse the data as JSON or not.
	 *
	 * @return string or decoded JSON object/array
	 */public function getCacheData($cache_name, $data_name, $force_last=false, $decode=true) {
		if ($decode) {
			$file_extension = '.json';
		} else {
			$file_extension = '.utf8';
		}
		$datafile = $this->cache_dir . '/' . $cache_name . '/' . $data_name . $file_extension;
		if ($this->cache_enabled && file_exists($datafile)) {
			if ($force_last || $this->getCacheExpirationFor($datafile) >= 0) {
				if ($decode) {
					return json_decode(@file_get_contents($datafile));
				} else {
					return @file_get_contents($datafile);
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Tests whether a given set of data has expired based on the passed duration.
	 *
	 * @return int (remaining time in seconds) or false
	 */private function getCacheExpirationFor($datafile, $cache_duration=900) {
		$expiration = @filemtime($datafile) + $cache_duration;
		if ($expiration) {
			$remaining = $expiration - time();
			return $remaining;
		} else {
			return false;
		}
	}
	
	/**
	 * Takes a cache name, data name, and URL — first looks for viable cache data, 
	 * then 
	 *
	 * @return int (remaining time in seconds) or false
	 */protected function getCachedURL($cache_name, $data_name, $data_url, $format='json', $decode=true) {
		$url_contents = $this->getCacheData($cache_name,$data_name,false,$decode);
		if (!$url_contents) {
			$url_contents = CASHSystem::getURLContents($data_url);
			if (!$url_contents) {
				$url_contents = $this->getCacheData($cache_name,$data_name,true,$decode);
			} else {
				if ($format == 'json') {
					$url_contents = json_decode($url_contents);
				}
				$this->setCacheData($cache_name,$data_name,$url_contents);
			}
		}
		return $url_contents;
	}
} // END class 
?>