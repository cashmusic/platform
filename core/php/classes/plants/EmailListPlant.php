<?php
/**
 * Plant handling assets: query information, handle download codes/passwords, etc
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
class EmailListPlant extends PlantBase {
	
	public function __construct($request_type,$request) {
		$this->request_type = 'emaillist';
		$this->plantPrep($request_type,$request);
	}
	
	public function processRequest() {
		if ($this->action) {
			switch ($this->action) {
				case 'signup':
					if (!$this->requireParameters('list_id')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('address')) { return $this->sessionGetLastResponse(); }
					if (filter_var($this->request['address'], FILTER_VALIDATE_EMAIL)) {
						if (isset($this->request['comment'])) {$initial_comment = $this->request['comment'];} else {$initial_comment = '';}
						if (isset($this->request['verified'])) {$verified = $this->request['verified'];} else {$verified = 0;}
						if (isset($this->request['name'])) {$name = $this->request['name'];} else {$name = 'Anonymous';}
						$result = $this->addAddress($this->request['address'],$this->request['list_id'],$initial_comment,$verified,$name);
						if ($result) {
							return $this->response->pushResponse(
								200,$this->request_type,$this->action,
								$this->request,
								'email address successfully added to list'
							);
						} else {
							return $this->response->pushResponse(
								500,$this->request_type,$this->action,
								$this->request,
								'there was an error adding an email to the list'
							);
						}
					} else {
						return $this->response->pushResponse(
							400,$this->request_type,$this->action,
							$this->request,
							'invalid email address'
						);
					}
					break;
					case 'viewlist':
						// REQUIRE DIRECT REQUEST!
						if (!$this->requireParameters('list_id')) { return $this->sessionGetLastResponse(); }
							$result = $this->getAddresses($this->request['list_id']);
							if ($result) {
								return $this->response->pushResponse(
									200,$this->request_type,$this->action,
									$result,
									'success. list included in payload'
								);
							} else {
								return $this->response->pushResponse(
									500,$this->request_type,$this->action,
									$this->request,
									'there was an error retrieving the list'
								);
							}
						break;
				default:
					return $this->response->pushResponse(
						400,$this->request_type,$this->action,
						$this->request,
						'unknown action'
					);
			}
		} else {
			return $this->response->pushResponse(
				400,
				$this->request_type,
				$this->action,
				$this->request,
				'no action specified'
			);
		}
	}
	
	/**
	 * Increments through an array based on $inc_by, wrapping at the end
	 *
	 * @param {string} $address -  the email address in question
	 * @return array|false
	 */public function getAddressInformation($address,$list_id) {
		$query = "SELECT * FROM emal_addresses WHERE email_address='$address' AND list_id=$list_id";
		return $this->db->doQueryForAssoc($query);
	}
	
	public function getAddresses($list_id,$limit=100,$start=0) {
		$query = "SELECT * FROM emal_addresses WHERE list_id=$list_id ORDER BY creation_date DESC LIMIT $start,$limit";
		return $this->db->doQueryForMultiAssoc($query);
	}

	public function addressIsVerified($address,$list_id) {
		$address_information = $this->getAddressInformation($address,$list_id);
		if (!$address_information) {
			return false; 
		} else {
			return $address_information['verified'];
		}
	}

	public function addAddress($address,$list_id,$initial_comment='',$verified=0,$name='Anonymous') {
		if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
			// first check to see if the email is already on the list
			if (!$this->getAddressInformation($address,$list_id)) {
				$initial_comment = strip_tags($initial_comment);
				$name = strip_tags($name);
				$creation_date = time();
				$query = "INSERT INTO emal_addresses (email_address,list_id,initial_comment,verified,name,creation_date) VALUES ('$address',$list_id,'$initial_comment',$verified,'$name',$creation_date)";
				if ($this->db->doQuery($query)) { 
					return true;
				} else {
					return false;
				}
			} else {
				// email already added. do no more.
				// 
				// REVISIT: consider overwriting the comment if new != old
				return true;
			}
		} else {
			return false;
		}
	}

	public function setAddressVerification($address,$list_id) {
		$verification_code = time();
		$query = "UPDATE emal_addresses SET verification_code='$verification_code',modification_date=$verification_code WHERE email_address='$address' AND list_id=$list_id";
		if ($this->db->doQuery($query)) { 
			return $verification_code;
		} else {
			return false;
		}
	}

	public function doAddressVerification($address,$list_id,$verification_code) {
		$alreadyverified = $this->addressIsVerified($address);
		if ($alreadyverified == 1) {
			$addressInfo = $this->getAddressInformation($address);
			return $addressInfo['id'];
		} else {
			$query = "SELECT * FROM emal_addresses WHERE email_address='$address' AND verification_code='$verification_code' AND list_id=$list_id";
			$result = $this->db->doQueryForAssoc($query);
			if ($result !== false) { 
				$id = $result['id'];
				$modified = time();
				$query = "UPDATE emal_addresses SET verified=1,modification_date=$modified WHERE id=$id";
				if ($this->db->doQuery($query)) { 
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