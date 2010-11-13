<?php
/**
 * No frills DB connection class
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
class MySQLSeed {
	protected $db;

	public function __construct($hostname,$username,$password,$database) {
		$this->db = mysql_connect($hostname,$username,$password) or die("Unable to connect to database");
		mysql_select_db($database, $this->db) or die("Named database was not found, unable to select database");
	}
	
	public function doQuery($query) {
		$result = mysql_query($query,$this->db);
		return $result;
	}
	
	public function doQueryForCount($query) {
		$result = mysql_query($query,$this->db);
		if ($result) {
			$rowcount = mysql_num_rows($result);
			return $rowcount;
		} else {
			return 0;
		}
	}

	public function doQueryForAssoc($query) {
		$result = mysql_query($query,$this->db);
		if ($result) {
			if (mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				mysql_free_result($result);
				return $row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function doQueryForMultiAssoc($query) {
		$result = mysql_query($query,$this->db);
		if ($result) {
			if (mysql_num_rows($result)) {
				$returnarray = array();
				while ($row = mysql_fetch_assoc($result)) {
					$returnarray[] = $row;
				}
				return $returnarray;
				mysql_free_result($result);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
} // END class 
?>