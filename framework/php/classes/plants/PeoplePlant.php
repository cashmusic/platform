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
 * violet           hope
 *
 **/
class PeoplePlant extends PlantBase {
	
	public function __construct($request_type,$request) {
		$this->request_type = 'people';
		$this->plantPrep($request_type,$request);
	}
	
	public function processRequest() {
		if ($this->action) {
			$this->routing_table = array(
				// alphabetical for ease of reading
				// first value  = target method to call
				// second value = allowed request methods (string or array of strings)
				'addaddresstolist'  => array('addAddress','direct'),
				'addlist'           => array('addList','direct'),
				'deletelist'        => array('deleteList','direct'),
				'editlist'          => array('editList','direct'),
				'getanalytics'      => array('getAnalytics','direct'),
				'getlistsforuser'   => array('getListsForUser','direct'),
				'getlist'           => array('getList',array('direct','api_key')),
				'getuser'           => array('getUser',array('direct','api_key')),
				'processwebhook'    => array('processWebhook',array('direct','api_key')),
				'signintolist'      => array('validateUserForList',array('post','direct','api_key')),
				'verifyaddress'     => array('doAddressVerification','direct'),
			);
			// see if the action matches the routing table:
			$basic_routing = $this->routeBasicRequest();
			if ($basic_routing !== false) {
				return $basic_routing;
			} else {
				switch ($this->action) {
					case 'signup':
						if (!$this->checkRequestMethodFor('direct','post','get','api_key')) return $this->sessionGetLastResponse();
						if (!$this->requireParameters('list_id','address')) { return $this->sessionGetLastResponse(); }
						if (isset($this->request['user_id'])) {
							$ownership = $this->verifyListOwner($this->request['user_id'],$this->request['list_id']);
							if (!$ownership) {
								return $this->response->pushResponse(
									403,$this->request_type,$this->action,
									null,
									'awfully presumptuous. you do not have permission to modify this list.'
								);
							}
						}
						if (filter_var($this->request['address'], FILTER_VALIDATE_EMAIL)) {
							if (isset($this->request['comment'])) {$initial_comment = $this->request['comment'];} else {$initial_comment = '';}
							if (isset($this->request['name'])) {$name = $this->request['name'];} else {$name = 'Anonymous';}
							if (isset($this->request['element_id'])) {
								$element_request = new CASHRequest(
									array(
										'cash_request_type' => 'element', 
										'cash_action' => 'getelement',
										'id' => $this->request['element_id']
									)
								);
								$do_not_verify = (bool) $element_request->response['payload']['options']->do_not_verify;
							} else {
								$do_not_verify = false;
							}
							$result = $this->addAddress($this->request['address'],$this->request['list_id'],$do_not_verify,$initial_comment,'',$name);
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
					case 'checkverification':
						if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
						if (!$this->requireParameters('address','list_id')) return $this->sessionGetLastResponse();
						$result = $this->addressIsVerified($this->request['address'],$this->request['list_id']);
						return $this->pushSuccess($result,'success. boolean included in payload');
						break;
					case 'viewlist':
						if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
						if (!$this->requireParameters('list_id')) { return $this->sessionGetLastResponse(); }
						$result = $this->getUsersForList($this->request['list_id']);
						if ($result) {
							$list_details = $this->getList($this->request['list_id']);
							$payload_data = array(
								'details' => $list_details,
								'members' => $result
							);
							return $this->pushSuccess($payload_data,'success. list included in payload');
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
			}
		} else {
			return $this->response->pushResponse(
				400,
				$this->request_type,
				$this->action,
				false,
				'no action specified'
			);
		}
	}

	/**
	 *
	 * LISTS
	 * Add, edit, and sync the actual lists themselves
	 *
	 */

	/**
	 * Adds a new list to the system
	 *
	 * @param {int} $list_id -      the list
	 * @param {int} $name -         a name given to the list for easy recognition
	 * @param {int} $description -  a description, in case the name is terrible and offers no help
	 * @param {int} $connection_id -  a third party connection with which the list should sync
	 * @return id|false
	 */protected function addList($name,$user_id,$description='',$connection_id=0) {
		$result = $this->db->setData(
			'people_lists',
			array(
				'name' => $name,
				'description' => $description,
				'user_id' => $user_id,
				'connection_id' => $connection_id
			)
		);
		if ($result) {
			$list_id = $result;
			$this->manageWebhooks($list_id,'add');
		}
		return $result;
	}

	/**
	 * Edits the details of a given list
	 *
	 * @param {int} $list_id -      the list
	 * @param {int} $name -         a name given to the list for easy recognition
	 * @param {int} $description -  a description, in case the name is terrible and offers no help
	 * @param {int} $connection_id -  a third party connection with which the list should sync
	 * @return id|false
	 */protected function editList($list_id,$name=false,$description=false,$connection_id=false) {
		$this->manageWebhooks($list_id,'remove');
		$final_edits = array_filter(
			array(
				'name' => $name,
				'description' => $description,
				'connection_id' => $connection_id
			),
			'CASHSystem::notExplicitFalse'
		);
		$result = $this->db->setData(
			'people_lists',
			$final_edits,
			array(
				"id" => array(
					"condition" => "=",
					"value" => $list_id
				)
			)
		);
		if ($result) {
			$this->manageWebhooks($list_id,'add');
		}
		return $result;
	}

	/**
	 * Removes an entire list and all member records. Use with caution.
	 *
	 * @param {int} $list_id - the list
	 * @return bool
	 */protected function deleteList($list_id) {
		$this->manageWebhooks($list_id,'remove');
		$result = $this->db->deleteData(
			'people_lists',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $list_id
				)
			)
		);
		if ($result) {
			// check and make sure that the list has addresses associated
			if ($this->getUsersForList($list_id)) {
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
	
	protected function getConnectionAPI($list_id) {
		$list_info     = $this->getList($list_id);
		// settings are called connections now
		$connection_id = $list_info['connection_id'];
		$user_id       = $list_info['user_id'];
		
		// if there is an external connection
		if ($connection_id) {
			$connection_type = $this->getConnectionType($connection_id);
			switch($connection_type) {
				case 'com.mailchimp':
					$mc = new MailchimpSeed($user_id, $connection_id);
					return array('connection_type' => $connection_type, 'api' => $mc);
					break;
				default:
					// unknown type
					return false;
			}
		} else {
			// no connection, return false
			return false;
		}
	}

	protected function manageWebhooks($list_id,$action='add') {
		$api_connection = $this->getConnectionAPI($list_id);
		if ($api_connection) {
			// connection found, api instantiated
			switch($api_connection['connection_type']) {
				case 'com.mailchimp':
					$mc = $api_connection['api'];
					// webhooks
					$api_credentials = CASHSystem::getAPICredentials();
					$webhook_api_url = CASH_API_URL . 'people/processwebhook/origin/com.mailchimp/list_id/' . $list_id . '/api_key/' . $api_credentials['api_key'];
					if ($action == 'remove') {
						return $mc->listWebhookDel($webhook_api_url);
					} else {
						return $mc->listWebhookAdd($webhook_api_url, $actions=null, $sources=null);
						// TODO: What do we do when adding a webhook fails?
						// TODO: Try multiple times?
					}
					break;
				default:
					// confused, return false
					return false;
			}
		} else {
			// no connection, simply return true
			return true;
		}
	}

	/**
	 * Does all the messy bits to make sure a list is synced with a 3rd-party
	 * email service if that's the kind of thing you're into...
	 *
	 */protected function doListSync($list_id, $api_url=false) {
		$list_info     = $this->getList($list_id);
		// settings are called connections now
		$connection_id = $list_info['connection_id'];
		$user_id       = $list_info['user_id'];

		if ($connection_id) {
			$connection_type = $this->getConnectionType($connection_id);
			switch($connection_type) {
				case 'com.mailchimp':
					$mc = new MailchimpSeed($user_id, $connection_id);
					
					$mailchimp_members = sort($mc->listMembers());
					// TODO: fix hard-coded limit...TO-DONE!
					$local_members	   = $this->getUsersForList($list_id,false);
					$mailchimp_count   = $mailchimp_members['total'];
					$local_count       = count($local_members);

					if ($local_count > 0 || $mailchimp_count > 0 ) {
						// test that sync is needed
						$remote_diff = array_diff($mailchimp_members, $local_members);
						$local_diff  = array_diff($local_members, $mailchimp_members);
						// TODO: implement these functions
						$this->addToRemoteList($list_id, $local_diff);
						$this->addToLocalList($list_id, $remote_diff);
					}
				default:
					return false;
			}
		}
	}

	/**
	 * Returns true or false that a user owns a given list
	 *
	 * @param {int} $user_id - the user
	 * @param {int} $list_id - the list
	 * @return bool
	 */protected function verifyListOwner($user_id,$list_id) {
		$list_details = $this->getList($this->request['id']);
		if ($list_details) {
			if ($list_details['user_id'] == $user_id) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Returns user information for a given list, including all signup data
	 *
	 * @param {int} $list_id -  the id of the list
	 * @param {int} $limit -    the number of users to return
	 * @param {int} $start -    start-at for the limit (pagination)
	 * @return array|false
	 */protected function getUsersForList($list_id,$limit=100,$start=0) {
		$query_limit = false;
		if ($limit) {
			$query_limit = "$start,$limit";
		}
		
		$result = $this->db->getData(
			'PeoplePlant_getUsersForList',
			false,
			array(
				"list_id" => array(
					"condition" => "=",
					"value" => $list_id
				)
			),
			$query_limit,
			'l.creation_date DESC' //this fix is less than ideal because it references the query alias l. ...but whatevs
		);
		return $result;
	}

	/**
	 * Returns all lists owned by a user
	 *
	 * @param {int} $user_id - the user
	 * @return array|false
	 */protected function getListsForUser($user_id) {
		$result = $this->db->getData(
			'people_lists',
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

	/**
	 * Returns basic information about a list
	 *
	 * @param {int} $list_id -     the id of the list
	 * @return array|false
	 */protected function getList($list_id) {
		$result = $this->db->getData(
			'people_lists',
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

	/**
	 * Pulls analytics queries in a few different formats
	 *
	 * @return array
	 */protected function getAnalytics($analtyics_type,$user_id=0,$list_id=false) {
		switch (strtolower($analtyics_type)) {
			case 'listmembership':
				$result = $this->db->getData(
					'PeoplePlant_getAnalytics_listmembership',
					false,
					array(
						"list_id" => array(
							"condition" => "=",
							"value" => $list_id
						)
					)
				);
				if ($result) {
					return $result[0];
				}
				break;
		}
		return false;
	}

	/**
	 *
	 * INDIVIDUAL USERS
	 * Add and remove individual users from a list, verify them, etc.
	 *
	 */

	protected function getUser($user_id) {
		$result = $this->db->getData(
			'users',
			'*',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			)
		);
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}

	/**
	 * Adds a user to a list. If no user exists for the email address passed, a
	 * new user will be created then added to the list.
	 *
	 * @param {string} $address -           the email address in question
	 * @param {int} $list_id -              the id of the list
	 * @param {bool} $verified -            0 for unverified, 1 to skip verification and mark ok
	 * @param {string} $initial_comment -   a comment passed with the list signup
	 * @param {string} $additional_data -   any extra data (JSON, etc) a dev might pass with signup for later use
	 * @param {string} $name -              if the user doesn't exist in the system this will be used as their display name
	 * @return bool
	 */protected function addAddress($address,$list_id,$do_not_verify=false,$initial_comment='',$additional_data='',$name='Anonymous',$force_verification_url=false) {
		if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
			// first check to see if the email is already on the list
			$user_id = $this->getUserIDForAddress($address);
			if (!$this->getAddressListInfo($address,$list_id)) {
				$initial_comment = strip_tags($initial_comment);
				$name = strip_tags($name);
				$user_id = $this->getUserIDForAddress($address);
				if (!$user_id) {
					$addlogin_request = new CASHRequest(
						array(
							'cash_request_type' => 'system', 
							'cash_action' => 'addlogin',
							'address' => $address,
							'password' => rand(23456,9876541),
							'display_name' => $name
						)
					);
					if ($addlogin_request->response['status_code'] == 200) {
						$user_id = $addlogin_request->response['payload'];
					} else {
						return false;
					}
				}
				if ($user_id) {
					$result = $this->db->setData(
						'list_members',
						array(
							'user_id' => $user_id,
							'list_id' => $list_id,
							'initial_comment' => $initial_comment,
							'verified' => 0,
							'active' => 1
						)
					);
					if ($result) {
						if ($do_not_verify) {
							$api_connection = $this->getConnectionAPI($list_id);
							$rc             = -1;
							if ($api_connection) {
								// connection found, api instantiated
								switch($api_connection['connection_type']) {
									case 'com.mailchimp':
										$mc = $api_connection['api'];
										// TODO: this is currently hardcoded to require a double opt-in
										$rc = $mc->listSubscribe($address, $merge_vars=null, $email_type=null, $double_optin=true);
										break;
								}
								if (!$rc) {
									// TODO: try again?
								}
							}
						} else {
							$list_details = $this->getList($list_id);
							$verification_code = $this->setAddressVerification($address,$list_id);
							$verification_url = $force_verification_url;
							if (!$verification_url) {
								$verification_url = CASHSystem::getCurrentURL();
							}
							$verification_url .= '?cash_request_type=people&cash_action=verifyaddress&address=' . urlencode($address) . '&list_id=' . $list_id . '&verification_code=' . $verification_code;
							CASHSystem::sendEmail(
								'Complete sign-up for: ' . $list_details['name'],
								CASHSystem::getDefaultEmail(),
								$address,
								'You requested to join the ' . $list_details['name'] . ' email list. If this message has been sent in error ignore it.'
									. 'To complete your sign-up simply visit: ' . "\n\n" . $verification_url,
								'Please confirm your membership'
							);
						}
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

	/**
	 * Sets a user inactive for a given list. If the user is not present on the 
	 * list it returns true.
	 *
	 * @param {string} $address -  the email address in question
	 * @param {int} $list_id -     the id of the list
	 * @return bool
	 */protected function removeAddress($address,$list_id) {
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
				if (!$result) {
					return false; // couldn't remove from the list
				}
			} 
			$api_connection = $this->getConnectionAPI($list_id);
			$rc = -1;
			if ($api_connection) {
				// connection found, api instantiated
				switch($api_connection['connection_type']) {
					case 'com.mailchimp':
						$mc = $api_connection['api'];
						$rc = $mc->listUnsubscribe($address);
						break;
				}
				if (!$rc) {
					// TODO: try again?
				}
			}
			// useer marked inactive, webhook removal attempts made
			return true;
		} else {
			// true for successful removal. user was never part of our list,
			// do nothing, do not attempt to sync
			return true;
		}
	}

	/**
	 * Returns true/false as to whether a user is verified for a specific list
	 *
	 * @param {string} $address -  the email address in question
	 * @param {int} $list_id -     the id of the list
	 * @return bool
	 */protected function addressIsVerified($address,$list_id) {
		$address_information = $this->getAddressListInfo($address,$list_id);
		if (!$address_information) {
			return false; 
		} else {
			return $address_information['verified'];
		}
	}

	protected function setAddressVerification($address,$list_id) {
		$verification_code = time();
		$user_id = $this->getUserIDForAddress($address);
		if ($user_id) {
			$result = $this->db->setData(
				'list_members',
				array(
					'verification_code' => $verification_code
				),
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
				return $verification_code;
			}
		}	
		return false;
	}

	protected function doAddressVerification($address,$list_id,$verification_code) {
		$user_id = $this->getUserIDForAddress($address);
		if ($user_id) {
			$already_verified = $this->addressIsVerified($address,$list_id);
			if ($already_verified) {
				$address_info = $this->getAddressListInfo($address,$list_id);
				return $address_info['id'];
			} else {
				$result = $this->db->getData(
					'list_members',
					'id',
					array(
						"user_id" => array(
							"condition" => "=",
							"value" => $user_id
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
				if ($result) { 
					$id = $result[0]['id'];
					$result = $this->db->setData(
						'list_members',
						array(
							'verified' => 1
						),
						array(
							"id" => array(
								"condition" => "=",
								"value" => $id
							)
						)
					);
					if ($result) { 
						$api_connection = $this->getConnectionAPI($list_id);
						$rc             = -1;
						if ($api_connection) {
							// connection found, api instantiated
							switch($api_connection['connection_type']) {
								case 'com.mailchimp':
									$mc = $api_connection['api'];
									// TODO: this is currently hardcoded to require a double opt-in
									$rc = $mc->listSubscribe($address, $merge_vars=null, $email_type=null, $double_optin=false);
									break;
							}
							if (!$rc) {
								// TODO: try again?
							}
						}
						return $id;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Returns email address information for a specific list / address
	 *
	 * @param {string} $address -  the email address in question
	 * @return array|false
	 */protected function getAddressListInfo($address,$list_id) {
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
	
	/**
	 * Returns user id for a given email address
	 *
	 * @param {string} $address -  the email address in question
	 * @return id|false
	 */protected function getUserIDForAddress($address) {
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

	protected function validateUserForList($address,$password,$browserid_assertion,$list_id,$element_id=null) {
		$validate = false;
		$verified_address = false;
		if ($browserid_assertion) {
			$address = CASHSystem::getBrowserIdStatus($browserid_assertion);
			if (!$address) {
				return false;
			} else {
				$verified_address = true;
			}
		}
		$user_id = $this->getUserIDForAddress($address);
		$list_info = $this->getList($list_id) ;
		$user_list_info = $this->getAddressListInfo($address,$list_id);
		if ($list_info['user_id'] == $user_id) {
			// user is the owner of the list, set validate to true
			$validate = true;
		}
		if ($user_list_info && !$validate) {
			// user is in the list, check that they're active then set validate to true
			if ($user_list_info['active'] == 1) {
				$validate = true;
			}
		}
		if ($validate) {
			$login_request = new CASHRequest(
				array(
					'cash_request_type' => 'system', 
					'cash_action' => 'validatelogin',
					'address' => $address, 
					'password' => $password,
					'verified_address' => $verified_address,
					'browserid_assertion' => $browserid_assertion,
					'require_admin' => false,
					'element_id' => $element_id
				)
			);
			if ($login_request->response['payload'] !== false) {
				return true;
			}
		}
		// we never validated, so automatically return false
		return false;
	}

	/**
	 *
	 * WEBHOOKS
	 * Handle incoming webhooks
	 *
	 */

	protected function processWebhook($incoming_request) {
		switch ($incoming_request['origin']) {
			case 'com.mailchimp':
				// make sure the API key matches the user_id of the list owner
				$ownership = $this->verifyListOwner($incoming_request['user_id'],$incoming_request['list_id']);
				if (!$ownership) {
					return false;
				}
				// matches. go:
				$mailchimp_type = $incoming_request['type'];
				$mailchimp_details = $incoming_request['data'];
				if ($mailchimp_type == 'subscribe') {
					$user_name = 'Anonymous';
					if (!empty($mailchimp_details['merges']['FNAME'])) {
						$user_name = $mailchimp_details['merges']['FNAME'] . ' ' . $mailchimp_details['merges']['LNAME'];
					}
					$result = $this->addAddress(
						$mailchimp_details['email'],
						$incoming_request['list_id'],
						1, // verified. trust all users from mailchimp
						'', // no initial comment
						'{"source":"com.mailchimp"}', // might as well store where it came from
						$user_name // this is the display name we put together up there a bit
					);
					if ($result) {
						return true;
					}
				} else if ($mailchimp_type == 'unsubscribe' || $mailchimp_type == 'cleaned') {
					// move user from active to inactive
					$result = $this->removeAddress($mailchimp_details['email'],$incoming_request['list_id']);
					if ($result) {
						return true;
					}
				} else if ($mailchimp_type ==  'upemail') {
					// update email address with data in $mailchimp_details
					// this is a do-later bit...editing a users email address...
				}
				break;
			default:
				return false;
		}
	}
} // END class 
?>
