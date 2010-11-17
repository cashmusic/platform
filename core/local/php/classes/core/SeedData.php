<?php
/**
 * Data access for all Seed classes, DB and Session handling
 *
 * $_SESSION['seed_last_reply']
 * $_SESSION['seed_persistent_store']
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
abstract class SeedData {
	protected $db=false;

	protected function connectDB() {
		$seed_db_settings = parse_ini_file(SEED_ROOT.'/settings/seed.ini.php');
		require_once(SEED_ROOT.'/classes/seeds/MySQLSeed.php');
		$this->db = new MySQLSeed(
			$seed_db_settings['hostname'],
			$seed_db_settings['username'],
			$seed_db_settings['password'],
			$seed_db_settings['database']
		);
	}

	protected function resetSeedSession() {
		$_SESSION['seed_last_reply'] = false;
		$_SESSION['seed_persistent_store'] = false;
		return true;
	}

	protected function sessionSetLastReply($reply,$reset_session_id=false) {
		if (!isset($_SESSION['seed_last_reply'])) {
			$this->resetSeedSession();
		}
		if ($reset_session_id) {
			session_regenerate_id(true);
		}
		$_SESSION['seed_last_reply'] = $reply;
		return true;
	}

	public function sessionGetLastReply() {
		if (!isset($_SESSION['seed_last_reply'])) {
			$this->resetSeedSession();
		}
		return $_SESSION['seed_last_reply'];
	}

	public function sessionClearLastReply() {
		$_SESSION['seed_last_reply'] = false;
		return true;
	}

	protected function sessionSetPersistent($key,$value) {
		if (!isset($_SESSION['seed_persistent_store'])) {
			$this->resetSeedSession();
		}
		session_regenerate_id(true);
		if (is_array($_SESSION['seed_persistent_store'])) {
			$_SESSION['seed_persistent_store']["$key"] = $value;
		} else {
			$_SESSION['seed_persistent_store'] = array("$key" => $value);
		}
	}

	public function sessionGetPersistent($key) {
		if (!isset($_SESSION['seed_persistent_store'])) {
			$this->resetSeedSession();
			return false;
		} else if (isset($_SESSION['seed_persistent_store']["$key"])) {
			return $_SESSION['seed_persistent_store']["$key"];
		} else {
			return false;
		}
	}

	public function sessionClearPersistent($key) {
		if (!isset($_SESSION['seed_persistent_store'])) {
			$this->resetSeedSession();
		} else if (isset($_SESSION['seed_persistent_store']["$key"])) {
			unset($_SESSION['seed_persistent_store']["$key"]);
		} 
	}

	public function sessionClearAllPersistent($varname) {
		$_SESSION['seed_persistent_store'] = false;
	}
} // END class 
?>