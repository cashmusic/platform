<?php
/**
 * PeoplePlant handles all user functions except login. It manages lists of users 
 * and will sync those lists between services.
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
class PeoplePlant extends PlantBase {
	
	public function __construct($request_type,$request) {
		$this->request_type = 'people';
		$this->plantPrep($request_type,$request);
	}
	
	public function processRequest() {
		if ($this->action) {
			switch ($this->action) {
				case 'signup':
					if (!$this->requireParameters('list_id','address')) { return $this->sessionGetLastResponse(); }
					if (filter_var($this->request['address'], FILTER_VALIDATE_EMAIL)) {
						if (isset($this->request['comment'])) {$initial_comment = $this->request['comment'];} else {$initial_comment = '';}
						if (isset($this->request['verified'])) {$verified = $this->request['verified'];} else {$verified = 0;}
						if (isset($this->request['name'])) {$name = $this->request['name'];} else {$name = 'Anonymous';}
						$result = $this->addAddress($this->request['address'],$this->request['list_id'],$verified,$initial_comment,'',$name);
						if ($result) {
							return $this->pushSuccess($this->request,'email address successfully added to list');
						} else {
							return $this->pushFailure('there was an error adding an email to the list');
						}
					} else {
						return $this->response->pushResponse(
							400,$this->request_type,$this->action,
							$this->request['address'],
							'invalid email address'
						);
					}
					break;
				case 'getlistsforuser':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('user_id')) return $this->sessionGetLastResponse();
						$result = $this->getListsForUser($this->request['user_id']);
						if ($result) {
							return $this->pushSuccess($result,'success. lists array included in payload');
						} else {
							return $this->pushFailure('no lists were found or there was an error retrieving the elements');
						}
					break;
				case 'addlist':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('user_id','list_name','list_description')) return $this->sessionGetLastResponse();
						$result = $this->addList($this->request['list_name'],$this->request['list_description'],$this->request['user_id']);
						if ($result) {
							return $this->pushSuccess($result,'success. lists added.');
						} else {
							return $this->pushFailure('there was an error adding the list.');
						}
					break;
				case 'viewlist':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('list_id')) { return $this->sessionGetLastResponse(); }
					$result = $this->getAddressesForList($this->request['list_id']);
					if ($result) {
						return $this->pushSuccess($result,'success. list included in payload');
					} else {
						return $this->pushFailure('there was an error retrieving the list');
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
	 */public function getAddressListInfo($address,$list_id) {
		$user_id = $this->getUserIDForAddress($address);
		if ($user_id) {
			$result = $this->db->getData(
				'list_members',
				'*',
				array(
					"user_id" => array(
						"condition" => "=",
						"value" => $user_id
					),
					"list_id" => array(
						"condition" => "=",
						"value" => $list_id
					)
				)
			);
			if ($result) {
				$return_array = $result[0];
				$return_array['email_address'] = $address;
				return $return_array;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function getUserIDForAddress($address) {
		$result = $this->db->getData(
			'users',
			'id',
			array(
				"email_address" => array(
					"condition" => "=",
					"value" => $address
				)
			)
		);
		if ($result) {
			return $result[0]['id'];
		} else {
			return false;
		}
	}
	
	public function getAddressesForList($list_id,$limit=100,$start=0) {
		$result = $this->db->getData(
			'PeoplePlant_getAddressesForList',
			false,
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

	public function getListsForUser($user_id) {
		$result = $this->db->getData(
			'user_lists',
			'*',
			array(
				"user_id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			)
		);
		return $result;
	}

	public function addressIsVerified($address,$list_id) {
		$address_information = $this->getAddressListInfo($address,$list_id);
		if (!$address_information) {
			return false; 
		} else {
			return $address_information['verified'];
		}
	}

	public function addList($name,$description,$user_id,$settings_id=0) {
		$result = $this->db->setData(
			'user_lists',
			array(
				'name' => $name,
				'description' => $description,
				'user_id' => $user_id,
				'settings_id' => $settings_id
			)
		);
		return $result;
	}

	public function addAddress($address,$list_id,$verified=0,$initial_comment='',$additional_data='',$name) {
		if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
			// first check to see if the email is already on the list
			$user_id = $this->getUserIDForAddress($address);
			if (!$this->getAddressListInfo($address,$list_id)) {
				$initial_comment = strip_tags($initial_comment);
				$name = strip_tags($name);
				$user_id = $this->getUserIDForAddress($address);
				if (!$user_id) {
					$user_id = $this->db->setData(
						'users',
						array(
							'email_address' => $address,
							'display_name' => $name
						)
					);
				}
				if ($user_id) {
					$result = $this->db->setData(
						'list_members',
						array(
							'user_id' => $user_id,
							'list_id' => $list_id,
							'initial_comment' => $initial_comment,
							'verified' => $verified,
						)
					);
				} else {
					return false;
				}
				return $result;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	public function setAddressVerification($address,$list_id) {
		/*
		 *
		 * --- MUST BE REWRITTEN, MAILER FUNCTIONS NEEDED IN THE CORE
		 *
		*/
		$verification_code = time();
		$result = $this->db->setData(
			'email_addresses',
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
		/*
		 *
		 * --- MUST BE REWRITTEN, MAILER FUNCTIONS NEEDED IN THE CORE
		 *
		*/
		$alreadyverified = $this->addressIsVerified($address);
		if ($alreadyverified == 1) {
			$addressInfo = $this->getAddressListInfo($address);
			return $addressInfo['id'];
		} else {
			$result = $this->db->getData(
				'email_addresses',
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
					'email_addresses',
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