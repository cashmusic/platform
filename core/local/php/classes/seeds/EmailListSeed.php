<?php
/**
 * Email list management
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
class EmailListSeed {
	protected $dbseed,$list_id;

	public function __construct($dbseed,$list_id) {
		$this->dbseed = $dbseed;
		$this->list_id = $list_id;
	}
	
	public function getAddressInformation($email) {
		$email = mysql_real_escape_string(strtolower($email));
		$query = "SELECT * FROM emal_addresses WHERE email_address='$email' AND list_id={$this->list_id}";
		return $this->dbseed->doQueryForAssoc($query);
	}
	
	public function getAddresses($limit=100,$start=0) {
		$query = "SELECT * FROM emal_addresses WHERE list_id={$this->list_id} LIMIT $start,$limit";
		return $this->dbseed->doQueryForMultiAssoc($query);
	}

	public function addressIsVerified($email) {
		$email_information = $this->getAddressInformation($email);
		if (!$email_information) {
			return false; 
		} else {
			return $email_information['verified'];
		}
	}

	public function addAddress($email,$initial_comment,$verified=0,$name='Anonymous') {
		$email = mysql_real_escape_string(strtolower($email));
		// first check to see if the email is already on the list
		if (!$this->getAddressInformation($email)) {
			$initial_comment = mysql_real_escape_string(strip_tags($initial_comment));
			$name = mysql_real_escape_string(strip_tags($name));
			if ($name == '') {
				$name = 'Anonymous';
			}
			$creation_date = time();
			$query = "INSERT INTO emal_addresses (email_address,list_id,initial_comment,verified,name,creation_date) VALUES ('$email',{$this->list_id},'$initial_comment',$verified,'$name',$creation_date)";
			if ($this->dbseed->doQuery($query)) { 
				return true;
			} else {
				return false;
			}
		} else {
			// email already added. do no more.
			return true;
		}
	}

	public function setAddressVerification($email) {
		$email = mysql_real_escape_string(strtolower($email));
		$verification_code = time();
		$query = "UPDATE emal_addresses SET verification_code='$verification_code',modification_date=$verification_code WHERE email_address='$email' AND list_id={$this->list_id}";
		if ($this->dbseed->doQuery($query)) { 
			return $verification_code;
		} else {
			return false;
		}
	}

	public function doAddressVerification($email,$verification_code) {
		$email = mysql_real_escape_string(strtolower($email));
		$alreadyverified = $this->addressIsVerified($email);
		if ($alreadyverified == 1) {
			$addressInfo = $this->getAddressInformation($email);
			return $addressInfo['id'];
		} else {
			$email = mysql_real_escape_string(strtolower($email));
			$query = "SELECT * FROM emal_addresses WHERE email_address='$email' AND verification_code='$verification_code' AND list_id={$this->list_id}";
			$result = $this->dbseed->doQueryForAssoc($query);
			if ($result !== false) { 
				$id = $result['id'];
				$modified = time();
				$query = "UPDATE emal_addresses SET verified=1,modification_date=$modified WHERE id=$id";
				if ($this->dbseed->doQuery($query)) { 
					return $id;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
} // END class 
?>