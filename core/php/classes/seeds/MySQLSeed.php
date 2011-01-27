<?php
/**
 * No frills DB connection class
 *
 * @package seed.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
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
	
	public function getData($tablename,$data,$conditions=false,$limit=false,$orderby=false) {
		$returnvalue = false;
		$query = false;
		if ($data) {
			$query = "SELECT $data FROM $tablename";
			if ($conditions) {
				$query .= " WHERE ";
				if (is_array($conditions)) {
					$separator = '';
					foreach ($conditions as $value) {
						$query .= $separator.$value;
						$separator = ' AND ';
					}
				} else {
					$query .= $conditions;
				}
				// Here we'll need to add an "AND user_id=$effective_user_id" for specific
				// tables. 
			}
			if ($orderby) {
				$query .= " ORDER BY $orderby";
			}
			if ($limit) {
				$query .= " LIMIT $limit";
			}
		}
		if ($query) {
			$result = mysql_query($query,$this->db);
			if ($result) {
				if (mysql_num_rows($result)) {
					$returnarray = array();
					while ($row = mysql_fetch_assoc($result)) {
						$returnarray[] = $row;
					}
					return $returnarray;
					mysql_free_result($result);
				}
			}
		}
		return $returnvalue;
	}
	
	public function setData($tablename,$data,$conditions=false) {
		$returnvalue = false;
		if (is_array($data)) {
			if ($conditions) {
				// if $condition is set then we're doing an UPDATE
				$modification_date = time();
				$query = "UPDATE $tablename SET ";
				$separator = '';
				foreach ($data as $fieldname => $value) {
				    if (is_string($value)) {
						$query .= $separator."$fieldname='".mysql_real_escape_string($value)."'";
					} else if (is_bool($value)) {
						$query .= $separator."$fieldname=".(int)$value;
					} else {
						$query .= $separator."$fieldname=$value";
					}
					$separator = ',';
				}
				$query .= ",modification_date=$modification_date WHERE ";
				if (is_array($conditions)) {
					$separator = '';
					foreach ($conditions as $value) {
						$query .= $separator.$value;
						$separator = ' AND ';
					}
				} else {
					$query .= $conditions;
				}
			// WHERE email_address='$address' AND list_id=$list_id";
			} else {
				echo "trying INSERT";
				// no condition? we're doing an INSERT
				$creation_date = time();
				$query = "INSERT INTO $tablename (";
				$separator = '';
				foreach ($data as $fieldname => $value) {
					$query .= $separator.$fieldname;
					$separator = ',';
				}
				$query .= ",creation_date) VALUES (";
				$separator = '';
				foreach ($data as $value) {
					if (is_string($value)) {
						$query .= $separator."'".mysql_real_escape_string($value)."'";
					} else if (is_bool($value)) {
						$query .= $separator.(int)$value;
					} else {
						$query .= $separator.$value;
					}
					$separator = ',';
				}
				$query .= ",$creation_date)";
			}
		} 
		if ($query) {
			$returnvalue = mysql_query($query,$this->db);
		}
		return $returnvalue;
	}
	
	public function doSpecialQuery($queryname) {
		
	}
} // END class 
?>