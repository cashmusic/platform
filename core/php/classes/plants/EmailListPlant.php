<?php
/**
 * Plant handling assets: query information, handle download codes/passwords, etc
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
	 * Returns email address information for a specific list / address
	 *
	 * @param {string} $address -  the email address in question
	 * @return array|false
	 */public function getAddressInformation($address,$list_id) {
		$result = $this->db->getData(
			'emal_addresses',
			'*',
			array(
				"email_address" => array(
					"condition" => "=",
					"value" => $address
				),
				"list_id" => array(
					"condition" => "=",
					"value" => $list_id
				)
			)
		);
		return $result[0];
	}
	
	public function getAddresses($list_id,$limit=100,$start=0) {
		$result = $this->db->getData(
			'emal_addresses',
			'*',
			array(
				"list_id" => array(
					"condition" => "=",
					"value" => $list_id
				)
			),
			"$start,$limit",
			'creation_date DESC'
		);
		return $result;
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
				$result = $this->db->setData(
					'emal_addresses',
					array(
						'email_address' => $address,
						'list_id' => $list_id,
						'initial_comment' => $initial_comment,
						'verified' => $verified,
						'name' => $name
					)
				);
				return $result;
			} else {
				// overwrite the name and comment if new != old? doing nothing
				// for now. maybe push into proper "user" status for updates?
				return true;
			}
		} else {
			return false;
		}
	}

	public function setAddressVerification($address,$list_id) {
		$verification_code = time();
		$result = $this->db->setData(
			'emal_addresses',
			array(
				'verification_code' => $verification_code
			),
			array(
				"email_address='$address'",
				"list_id=$list_id"
			)
		);
		if ($result) { 
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
			$result = $this->db->getData(
				'emal_addresses',
				'*',
				array(
					"email_address" => array(
						"condition" => "=",
						"value" => $address
					),
					"verification_code" => array(
						"condition" => "=",
						"value" => $verification_code
					),
					"list_id" => array(
						"condition" => "=",
						"value" => $list_id
					)
				)
			);
			if ($result !== false) { 
				$id = $result[0]['id'];
				$result = $this->db->setData(
					'emal_addresses',
					array(
						'verified' => 1
					),
					"id=$id"
				);
				if ($result) { 
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