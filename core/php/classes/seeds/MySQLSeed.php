<?php
/**
 * No frills DB connection class
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
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
	
	protected function doQueryForArray($query) {
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
			} else {
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
			if ($returnvalue) {
				$returnvalue = mysql_insert_id();
			} 
		}
		return $returnvalue;
	}
	
	public function doSpecialQuery($query_name,$query_options=false) {
		switch ($query_name) {
			case 'AssetPlant_getAssetInfo':
				$query = "SELECT a.user_id,a.parent_id,a.location,a.title,a.description,a.comment,a.seed_settings_id,";
				$query .= "s.name,s.type ";
				$query .= "FROM asst_assets a LEFT OUTER JOIN seed_settings s ON a.seed_settings_id = s.id ";
				$query .= "WHERE a.id = {$query_options['asset_id']}";
				return $this->doQueryForArray($query);
				break;
			case 'EventPlant_getAllDates':
				$query = "SELECT d.id,u.display_name as user_display_name,d.date,d.publish,d.cancelled,d.comments,";
				$query .= "v.name as venuename,v.address1,v.address2,v.city,v.region,v.country,v.postalcode,v.website,v.phone ";
				$query .= "FROM live_events d JOIN live_venues v ON d.venue_id = v.id JOIN seed_users u ON d.user_id = u.id ";
				$query .= "WHERE d.date > {$query_options['cutoffdate']} AND u.id = {$query_options['user_id']} ORDER BY d.date ASC";
				return $this->doQueryForArray($query);
				break;
		    case 'EventPlant_getDatesBetween':
				$query = "SELECT d.id,u.display_name as user_display_name,d.date,d.publish,d.cancelled,d.comments,";
				$query .= "v.name as venuename,v.address1,v.address2,v.city,v.region,v.country,v.postalcode,v.website,v.phone ";
				$query .= "FROM live_events d JOIN live_venues v ON d.venue_id = v.id JOIN seed_users u ON d.user_id = u.id ";
				$query .= "WHERE d.date > {$query_options['afterdate']} AND d.date < {$query_options['beforedate']} ";
				$query .= "AND u.id = {$query_options['user_id']} ORDER BY d.date ASC";
				return $this->doQueryForArray($query);
				break;
			case 'EventPlant_getDatesByArtistAndDate':
				$query = "SELECT d.id,u.display_name as user_display_name,d.date,d.publish,d.cancelled,d.comments";
				$query .= "v.name as venuename,v.address1,v.address2,v.city,v.region,v.country,v.postalcode,v.website,v.phone ";
				$query .= "FROM live_events d JOIN live_venues v ON d.venue_id = v.id JOIN seed_users u ON d.user_id = u.id ";
				$query .= "WHERE d.date = {$query_options['date']} AND u.id = {$query_options['user_id']} ORDER BY d.date ASC";
				return $this->doQueryForArray($query);
				break;
		    default:
		       return false;
		}
	}
} // END class 
?>