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
					if (!$this->checkRequestMethodFor('direct','post','api_key')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('list_id','address')) { return $this->sessionGetLastResponse(); }
					if (isset($this->request['user_id'])) {
						$list_auth_request = new CASHRequest(
							array(
								'cash_request_type' => 'people', 
								'cash_action' => 'getlistinfo',
								'id' => $this->request['list_id']
							)
						);
						if ($list_auth_request->response['status_code'] == '200') {
							if ($list_auth_request->response['payload']['user_id'] != $this->request['user_id']) {
								return $this->response->pushResponse(
									403,$this->request_type,$this->action,
									null,
									'awfully presumptuous. you do not have permission to modify this list.'
								);
							}
						}
					}
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
						$settings_id = 0;
						if (isset($this->request['settings_id'])) {
							$settings_id = (int) $this->request['settings_id'];
						}
						$result = $this->addList($this->request['list_name'],$this->request['list_description'],$this->request['user_id'],$settings_id);
						if ($result) {
							return $this->pushSuccess($result,'success. lists added.');
						} else {
							return $this->pushFailure('there was an error adding the list.');
						}
					break;
				case 'editlist':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('list_id','list_name','list_description')) return $this->sessionGetLastResponse();
						$settings_id = 0;
						if (isset($this->request['settings_id'])) {
							$settings_id = (int) $this->request['settings_id'];
						}
						$result = $this->editList($this->request['list_id'],$this->request['list_name'],$this->request['list_description'],$settings_id);
						if ($result) {
							return $this->pushSuccess($result,'success. lists edited.');
						} else {
							return $this->pushFailure('there was an error editing the list.');
						}
					break;
				case 'viewlist':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('list_id')) { return $this->sessionGetLastResponse(); }
					$result = $this->getAddressesForList($this->request['list_id']);
					if ($result) {
						$list_details = $this->getListById($this->request['list_id']);
						$payload_data = array(
							'details' => $list_details,
							'members' => $result
						);
						return $this->pushSuccess($payload_data,'success. list included in payload');
					} else {
						return $this->pushFailure('there was an error retrieving the list');
					}
					break;
				case 'deletelist':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('list_id')) { return $this->sessionGetLastResponse(); }
					$result = $this->deleteList($this->request['list_id']);
					if ($result) {
						return $this->pushSuccess($result,'success. list and list members removed.');
					} else {
						return $this->pushFailure('there was an error retrieving the list');
					}
					break;
				case 'getlistinfo':
					if (!$this->checkRequestMethodFor('direct','api_key')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('id')) { return $this->sessionGetLastResponse(); }
					$result = $this->getListById($this->request['id']);
					if ($result) {
						return $this->pushSuccess($result,'success. list info included in payload');
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
			'l.creation_date DESC' //this fix is less than ideal because it references the query alias l. ...but whatevs
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

	public function getListById($list_id) {
		$result = $this->db->getData(
			'user_lists',
			'*',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $list_id
				)
			)
		);
		if ($result) {
			return $result[0];
		}
		return $result;
	}

	public function deleteList($list_id) {
		$result = $this->db->deleteData(
			'user_lists',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $list_id
				)
			)
		);
		if ($result) {
			// check and make sure that the list has addresses associated
			if ($this->getAddressesForList($list_id)) {
				// it does? delete them
				$result = $this->db->deleteData(
					'list_members',
					array(
						'list_id' => array(
							'condition' => '=',
							'value' => $list_id
						)
					)
				);
			}
		}
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

	public function editList($list_id,$name,$description,$settings_id=0) {
		$result = $this->db->setData(
			'user_lists',
			array(
				'name' => $name,
				'description' => $description,
				'settings_id' => $settings_id
			),
			array(
				"id" => array(
					"condition" => "=",
					"value" => $list_id
				)
			)
		);
		return $result;
	}

	public function doListSync($list_id,$pull=true,$push=false) {
		/*
		We should call this function whenever a list is first synced to a remote
		source. If part of an addlist call we only need to do a pull. If it's a
		new sync added to an existing list then we should, in order:
		
		 - first test to see if any members are present on the list, if so store them
		 - do a pull from remote list and add all members
		 - if the initial test found members, push them to the remote list
		
		 - if remote list supports webhooks we should set those up here (if possible)
		   to enable 2-way sync
		*/
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
							'active' => 1
						)
					);
					if ($result) {
						/*
						Check for list sync. If found, use the appropriate seed
						to add the user to the remote list
						*/
					}
					return $result;
				}
			} else {
				// address already present, do nothing but return true
				return true;
			}
		}
		return false;
	}

	public function removeAddress($address,$list_id) {
		$membership_info = $this->getAddressListInfo($address,$list_id);
		if ($membership_info) {
			if ($membership_info['active']) {
				$result = $this->db->setData(
					'list_members',
					array(
						'active' => 0
					),
					array(
						"id" => array(
							"condition" => "=",
							"value" => $membership_info['id']
						)
					)
				);
				if ($result) {
					/*
					Check for list sync. If found, use the appropriate seed
					to remove the user to the remote list
					*/
				}
				return $result;
			} else {
				/*
				user found on our list but already marked inactive. we should
				now check to see if the list is synced to another list remotely. if
				so, use the appropriate seed to remove user remotely
				*/
				return true;
			}
		} else {
			// true for successful removal. user was never part of our list,
			// do nothing, do not attempt to sync
			return true;
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