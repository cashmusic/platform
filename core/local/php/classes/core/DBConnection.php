<?php
/**
 * Base for all Seed classes
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmuisc.org/
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
abstract class DBConnection {
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
} // END class 
?>