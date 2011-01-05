<?php
/**
 * Email list management
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
class EmailListSeed extends SeedBase {
	protected $list_id;

	public function __construct($list_id) {
		$this->connectDB();
		$this->list_id = $list_id;
	}
	
	public function getAddressInformation($address) {
		$query = "SELECT * FROM emal_addresses WHERE email_address='$address' AND list_id={$this->list_id}";
		return $this->dbseed->doQueryForAssoc($query);
	}
	
	public function getAddresses($limit=100,$start=0) {
		$query = "SELECT * FROM emal_addresses WHERE list_id={$this->list_id} LIMIT $start,$limit";
		return $this->dbseed->doQueryForMultiAssoc($query);
	}

	public function addressIsVerified($address) {
		$address_information = $this->getAddressInformation($address);
		if (!$address_information) {
			return false; 
		} else {
			return $address_information['verified'];
		}
	}

	public function addAddress($address,$initial_comment,$verified=0,$name='Anonymous') {
		// first check to see if the email is already on the list
		if (!$this->getAddressInformation($address)) {
			$initial_comment = strip_tags($initial_comment);
			$name = strip_tags($name);
			if ($name == '') {
				$name = 'Anonymous';
			}
			$creation_date = time();
			$query = "INSERT INTO emal_addresses (email_address,list_id,initial_comment,verified,name,creation_date) VALUES ('$address',{$this->list_id},'$initial_comment',$verified,'$name',$creation_date)";
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

	public function setAddressVerification($address) {
		$verification_code = time();
		$query = "UPDATE emal_addresses SET verification_code='$verification_code',modification_date=$verification_code WHERE email_address='$address' AND list_id={$this->list_id}";
		if ($this->dbseed->doQuery($query)) { 
			return $verification_code;
		} else {
			return false;
		}
	}

	public function doAddressVerification($address,$verification_code) {
		$alreadyverified = $this->addressIsVerified($address);
		if ($alreadyverified == 1) {
			$addressInfo = $this->getAddressInformation($address);
			return $addressInfo['id'];
		} else {
			$query = "SELECT * FROM emal_addresses WHERE email_address='$address' AND verification_code='$verification_code' AND list_id={$this->list_id}";
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