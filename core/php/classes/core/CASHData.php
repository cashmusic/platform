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
	 * SETTINGS INFO
	 * List what settings are available and what specific formats those settings
	 * contain. Metadata, not individual stored settings.
	 *
	 */

	/**
	 * Finds all settings type JSON files, builds an array keyed by type
	 *
	 * @return array
	 */public function getSettingsTypes() {
		if ($settings_dir = opendir(CASH_PLATFORM_ROOT.'/settings/types')) {
			$settings_types = array();
			while (false !== ($file = readdir($settings_dir))) {
				if (substr($file,0,1) != "." && !is_dir($file)) {
					$tmpKey = strtolower(substr_replace($file, '', -5));
					$settings_types["$tmpKey"] = json_decode(file_get_contents(CASH_PLATFORM_ROOT.'/settings/types/'.$file));
				}
			}
			closedir($settings_dir);
			return $settings_types;
		} else {
			return false;
		}
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
} // END class 
?>