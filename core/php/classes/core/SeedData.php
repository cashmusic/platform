<?php
/**
 * Data access for all Seed classes, DB and Session handling
 *
 * @package seed.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */abstract class SeedData {
	protected $db=false;

	/**
	 * Grabs database connection properties from /settings/seed.ini.php and
	 * opens the appropriate connection
	 *
	 * @return void
	 */protected function connectDB() {
		$seed_db_settings = parse_ini_file(SEED_ROOT.'/settings/seed.ini.php');
		require_once(SEED_ROOT.'/classes/seeds/MySQLSeed.php');
		$this->db = new MySQLSeed(
			$seed_db_settings['hostname'],
			$seed_db_settings['username'],
			$seed_db_settings['password'],
			$seed_db_settings['database']
		);
	}

	/**
	 * Empties (or creates empty) entries to the standard $_SESSION array
	 *
	 * @return boolean
	 */protected function resetSeedSession() {
		$_SESSION['seed_last_response'] = false;
		$_SESSION['seed_persistent_store'] = false;
		return true;
	}

	/**
	 * Replaces $_SESSION['seed_last_response'] with a new response
	 *
	 * @param {array} $response - the new SeedResponse
	 * @param {boolean} $reset_session_id [default: false] - if true a new 
	 *        session id is generated as a security measure 
	 * @return boolean
	 */protected function sessionSetLastResponse($response,$reset_session_id=false) {
		if (!isset($_SESSION['seed_last_response'])) {
			$this->resetSeedSession();
		}
		if ($reset_session_id) {
			session_regenerate_id(true);
		}
		$_SESSION['seed_last_response'] = $response;
		return true;
	}

	/**
	 * Returns the current value of $_SESSION['seed_last_response']
	 *
	 * @return array|false
	 */public function sessionGetLastResponse() {
		if (!isset($_SESSION['seed_last_response'])) {
			$this->resetSeedSession();
		}
		return $_SESSION['seed_last_response'];
	}

	/**
	 * Sets $_SESSION['seed_last_response'] to false
	 *
	 * @return array|false
	 */public function sessionClearLastResponse() {
		$_SESSION['seed_last_response'] = false;
		return true;
	}

	/**
	 * Adds new data to the $_SESSION['seed_persistent_store'] array and resets
	 * the session id as a security precaution. 
	 *
	 * @param {string} $key - the key to associate with the new data
	 * @param {*} $value - the data to store
	 * @return boolean
	 */protected function sessionSetPersistent($key,$value) {
		if (!isset($_SESSION['seed_persistent_store'])) {
			$this->resetSeedSession();
		}
		if (is_array($_SESSION['seed_persistent_store'])) {
			$_SESSION['seed_persistent_store'][(string)$key] = $value;
		} else {
			$_SESSION['seed_persistent_store'] = array((string)$key => $value);
		}
		$_SESSION['seed_persistent_store']['session_regenerate_id'] = true;
		return true;
	}

	/**
	 * Returns data from the $_SESSION['seed_persistent_store'] array
	 *
	 * @param {string} $key - the key associated with the requested data
	 * @return *|false
	 */public function sessionGetPersistent($key) {
		if (!isset($_SESSION['seed_persistent_store'])) {
			$this->resetSeedSession();
			return false;
		} 
		if (isset($_SESSION['seed_persistent_store'][(string)$key])) {
			return $_SESSION['seed_persistent_store'][(string)$key];
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
		if (!isset($_SESSION['seed_persistent_store'])) {
			$this->resetSeedSession();
		} else if (isset($_SESSION['seed_persistent_store'][(string)$key])) {
			unset($_SESSION['seed_persistent_store'][(string)$key]);
		} 
	}

	/**
	 * Removes all data from $_SESSION['seed_persistent_store'], setting it false
	 *
	 * @param {string} $key - the key to be removed
	 * @return void
	 */public function sessionClearAllPersistent($varname) {
		$_SESSION['seed_persistent_store'] = false;
	}
} // END class 
?>