<?php
/**
 * PeoplePlant handles all user functions except login. It manages lists of users
 * and will sync those lists between services.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 * violet           hope
 *
 **/

namespace CASHMusic\Plants\People;

use CASHMusic\Core\PlantBase;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Entities\Element;
use CASHMusic\Entities\PeopleList;
use CASHMusic\Entities\PeopleListsMember;
use CASHMusic\Entities\PeopleMailing;
use CASHMusic\Entities\PeopleMailingsAnalytic;
use CASHMusic\Seeds\MailchimpSeed;
use CASHMusic\Entities\People;

class PeoplePlant extends PlantBase {

	public function __construct($request_type,$request) {
		$this->request_type = 'people';
        $this->getRoutingTable();

		$this->plantPrep($request_type,$request);
	}

	/**
	 * Get recent activity for a given user. (list joins and orders) —
	 * if since_date isn't set it will default to two weeks from now.
	 *
	 * @return array|bool
	 */protected function getRecentActivity($user_id,$since_date=0) {
		if ($since_date == 0) {
			$since_date = time() - 60480;
		}
		// create an array to return
		$return_array = array();
		// get orders for the timeframe
		$order_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'getordersforuser',
				'user_id' => $user_id,
				'since_date' => $since_date
			)
		);
		$return_array['orders'] = $order_request->response['payload'];

		// get list activity (new joins)

		$result = $this->db->table('people_lists_members')
			->selectDistinct('people_lists_members.list_id')
            ->select([$this->db->raw("COUNT(people_lists_members.list_id) AS total"), "people_lists.name"])
			->join('people_lists', 'people_lists_members.list_id', '=', 'people_lists.id')
			->where('people_lists.user_id', $user_id)
		    ->where('people_lists_members.active', 1)
			->where('people_lists_members.creation_date', '>', $since_date)
			->groupBy('people_lists_members.list_id')->get();

		$return_array['lists'] = $result;

		return $return_array;
	}

	/**
	 *
	 * LISTS
	 * Add, edit, and sync the actual lists themselves
	 *
	 */

	/**
	 * Adds an email address to an existing list — optionally tie to a specific element for analytics
	 */
	protected function doSignup($list_id,$address,$user_id=false,$comment='',$name='Anonymous',$element_id=false,$first_name='',$last_name='',$additional_data='') {
		if ($user_id) {
			$ownership = $this->verifyListOwner($user_id,$list_id);
			if (!$ownership) {
				return false;
			}
		}
		if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
			if ($element_id) {
				$element = Element::find($element_id);
				$do_not_verify = (bool) $element->options['do_not_verify'];
			} else {
				$do_not_verify = false;
			}
			$result = $this->addAddress($address,$list_id,$do_not_verify,$comment,'',$name,false,false,true,'&element_id='.$element_id,$first_name,$last_name,$additional_data);
			return $result;
		} else {
			return false;
		}
	}

	protected function viewList($list_id,$unlimited=false,$user_id=false) {
		if ($unlimited) {
			$result = $this->getUsersForList($list_id,false);
		} else {
			$result = $this->getUsersForList($list_id);
		}
		if ($result) {
			$list_details = $this->getList($list_id);
			if ($user_id) {
				if ($list_details['user_id'] != $user_id) {
					return false;
				}
			}
			$payload_data = array(
				'details' => $list_details,
				'members' => $result
			);
			return $payload_data;
		} else {
			return false;
		}
	}

	/**
	 * Adds a new list to the system
	 *
	 * @param {int} $list_id -      the list
	 * @param {int} $name -         a name given to the list for easy recognition
	 * @param {int} $description -  a description, in case the name is terrible and offers no help
	 * @param {int} $connection_id -  a third party connection with which the list should sync
	 * @return id|false
	 */protected function addList($name,$user_id,$description='',$connection_id=0) {
		$result = PeopleList::create([
            'name' => $name,
            'description' => $description,
            'user_id' => $user_id,
            'connection_id' => $connection_id
		]);

		if ($result) {
			$this->manageWebhooks($result->id,'add');
            return $result->id;
		} else {
			return false;
		}
	}

	/**
	 * Edits the details of a given list
	 *
	 * @param {int} $list_id -      the list
	 * @param {int} $name -         a name given to the list for easy recognition
	 * @param {int} $description -  a description, in case the name is terrible and offers no help
	 * @param {int} $connection_id -  a third party connection with which the list should sync
	 * @return id|false
	 */
	protected function editList($list_id,$name=false,$description=false,$connection_id=false,$user_id=false) {

        $final_edits = array_filter(
            array(
                'name' => $name,
                'description' => $description,
                'connection_id' => $connection_id
            ),
            function($value) {
                return CASHSystem::notExplicitFalse($value);
            }
        );

        try {
            if ($user_id) {
                $list = PeopleList::findWhere(['id'=>$list_id, 'user_id'=>$user_id]);
            } else {
                $list = PeopleList::find($list_id);
            }

            if ($list) {
                $list->update($final_edits);
			}

        } catch (\Exception $e) {
            if (CASH_DEBUG) {
                CASHSystem::errorLog($e->getMessage());
            }
            return false;
        }

		if ($list && $connection_id) {
			// remove then add id connection_id has changed
			$this->manageWebhooks($list_id,'remove');
			$this->manageWebhooks($list_id,'add');
		}
		return $list;
	}

	/**
	 * Removes an entire list and all member records. Use with caution.
	 *
	 * @param {int} $list_id - the list
	 * @return bool
	 */
	protected function deleteList($list_id,$user_id=false) {

		try {
			if ($user_id) {
				$list = PeopleList::findWhere(['id'=>$list_id, 'user_id'=>$user_id]);
			} else {
				$list = PeopleList::find($list_id);
			}

			if ($list) {
				$list->delete();
			}

		} catch (\Exception $e) {
			if (CASH_DEBUG) {
				CASHSystem::errorLog($e->getMessage());
			}
			return false;
		}

		if ($list) {
			$this->manageWebhooks($list_id,'remove');
			// check and make sure that the list has addresses associated
			if ($this->getUsersForList($list_id)) {
				// it does? delete them
                $result = $this->db->table('people_lists_members')->where('list_id', $list_id)->delete();
			}

			return true;
		} else {
			return false;
		}

	}

	protected function getConnectionAPI($list_id) {
		$list_info     = $this->getList($list_id);

		$list_info = $list_info->toArray();
		// settings are called connections now
		$connection_id = $list_info['connection_id'];
		$user_id       = $list_info['user_id'];

		// if there is an external connection
		if ($connection_id) {
			$connection_type = $this->getConnectionType($connection_id);
			switch($connection_type) {
				case 'com.mailchimp':
					//
					//
					//

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
					$webhook_api_url = CASH_API_URL . '/verbose/people/processwebhook/origin/com.mailchimp/list_id/' . $list_id . '/api_key/' . $api_credentials['api_key'];
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
		$list_details = $this->getList($list_id);
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

    	$result = $this->db->table('people')
			->select(['people.id', 'people.email_address', 'people.display_name', 'people.first_name', 'people.last_name', 'members.initial_comment', 'members.additional_data', 'members.active', 'members.verified', 'members.creation_date'])
			->join('people_lists_members as members', 'members.user_id', '=', 'people.id', 'LEFT OUTER')
			->where('members.list_id', $list_id)
			->where('members.active', 1)
			->orderBy("members.creation_date", "DESC")
			->limit($query_limit)->get();

		return $result;
	}

	/**
	 * Returns all lists owned by a user
	 *
	 * @param {int} $user_id - the user
	 * @return array|false
	 */protected function getListsForUser($user_id) {

		$result = PeopleList::findWhere(['user_id'=>$user_id]);

		return $result;
	}

	/**
	 * Returns basic information about a list
	 *
	 * @param {int} $list_id -     the id of the list
	 * @return array|false
	 */
	protected function getList($list_id,$user_id=false) {

		if ($user_id) {
			$list = PeopleList::findWhere(['id'=>$list_id, 'user_id'=>$user_id]);
		} else {
			$list = PeopleList::find($list_id);
		}

		if ($list) {
			return $list;
		} else {
            return false;
		}
	}

	/**
	 * Pulls analytics queries in a few different formats
	 *
	 * @return array
	 */protected function getAnalytics($analtyics_type,$user_id=0,$list_id=false) {
		switch (strtolower($analtyics_type)) {
			case 'listmembership':

                $result = $this->db->table('people_lists_members')
					->select([
						$this->db->raw('COUNT(*) AS total'),
						$this->db->raw('COUNT(CASE WHEN active = 1 THEN 1 END) AS active'),
						$this->db->raw('COUNT(CASE WHEN active = 0 THEN 1 END) AS inactive'),
						$this->db->raw('COUNT(CASE WHEN creation_date > " . (time() - 604800) . " THEN 1 END) AS last_week')])
					->where("list_id", $list_id)->get();

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

	/**
	 * Gets details for an individual user
	 *
	 */
	protected function getUser($user_id) {
	 	if ($user = People::find($user_id)) {
	 		return $user->toArray();
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
	 */
	protected function addAddress($address,$list_id,$do_not_verify=false,$initial_comment='',$additional_data='',$name='Anonymous',$force_verification_url=false,$request_from_service=false,$service_opt_in=true,$extra_querystring='',$first_name='',$last_name='') {
		if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
			// first check to see if the email is already on the list
			$take_action = false;
			$user_id = $this->getUserIDForAddress($address);
			if ($user_id) {
				$user_info = $this->getAddressListInfo($address,$list_id);
				if (!$user_info) {
					$take_action = 'addandemail';
				} else {
					if (!$user_info['verified']) {
						$take_action = 'onlyemail';
					}
				}
			} else {
				$take_action = 'addandemail';
			}
			if ($take_action) {
				$initial_comment = strip_tags($initial_comment);
				$name = strip_tags($name);
				//$user_id = $this->getUserIDForAddress($address);
				if (!$user_id) {
					if ($name='Anonymous' && (!empty($first_name) || !empty($last_name))) {
						$name = trim($first_name . ' ' . $last_name);
					}

					$addlogin_request = new CASHRequest(
						array(
							'cash_request_type' => 'system',
							'cash_action' => 'addlogin',
							'address' => $address,
							'password' => md5(rand(23456,9876541)),
							'display_name' => $name,
							'first_name' => $first_name,
							'last_name' => $last_name
						)
					);
					if ($addlogin_request->response['status_code'] == 200) {
						$user_id = $addlogin_request->response['payload'];
					} else {
						return false;
					}
				}
				if ($user_id) {
					if ($take_action != 'onlyemail') {

						$result = PeopleListsMember::create([
                            'user_id' => $user_id,
                            'list_id' => $list_id,
                            'initial_comment' => $initial_comment,
                            'additional_data' => $additional_data,
                            'verified' => 0,
                            'active' => 1
						]);

					} else {
						$result = true;
					}
					if ($result && !$request_from_service) {
						if ($do_not_verify) {
							$api_connection = $this->getConnectionAPI($list_id);
							if ($api_connection) {
								// connection found, api instantiated
								switch($api_connection['connection_type']) {
									case 'com.mailchimp':
										$mc = $api_connection['api'];
										// mailchimp found. subscribe user and request opt-in
										// error_log(json_encode($mc));
										$rc = $mc->listSubscribe($address, array('double_optin' => $service_opt_in));
										// error_log(json_encode($rc));
										break;
								}
							}
						} else {
							$list_details = $this->getList($list_id);
							$verification_code = $this->setAddressVerification($address,$list_id);
							$verification_url = $force_verification_url;
							if (!$verification_url) {
								$verification_url = CASHSystem::getCurrentURL();
							}
							$verification_url .= '?cash_request_type=people&cash_action=verifyaddress&address=' . urlencode($address) . '&list_id=' . $list_id . '&verification_code=' . $verification_code . $extra_querystring;

							CASHSystem::sendEmail(
								'Complete sign-up for: ' . $list_details['name'],
								$list_details['user_id'],
								$address,
								'You requested to join the ' . $list_details['name'] . ' list. If this message has been sent in error ignore it.'
                           . 'To complete your sign-up: ' . "\n\n [Verify your email address](" . $verification_url . ") \n\nNote to iOS users: you can only download on your computers, then sync to your device. Downloads will not work if on your iPhone or iPad.",
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

	protected function addBulkAddresses($addresses) {

		$address_insert = [];
		foreach ($addresses as $address) {
            $address_insert[] = [
				'email_address' => trim($address),
				'username' => trim($address),
				'password'=> md5(rand(23456,9876541)),
				'data' => 'bulk_import',
				'creation_date' => time()
			];
		}

		// bulk create users
		$create_users = $this->db->table('people')->insertIgnore($address_insert);

        if ($create_users) {
            // query users with "bulk_import" as data field.
			$get_created_users = People::findWhere(['data' => 'bulk_import']);

            $created_user_ids = [];
            $created_user_emails = [];

            // stash user ids and emails.
			foreach ($get_created_users as $user) {
                $created_user_ids[] = $user->id;
                $created_user_emails[] = $user->email_address;
			}

            // compare emails with original $addresses array.
			if (count($created_user_emails) < count($addresses)) {
				$remaining_emails = array_diff($addresses, $created_user_emails);

				// let's double check that these are valid emails, save a query
                $remaining_emails = filter_var_array($remaining_emails,FILTER_VALIDATE_EMAIL);

                if (count($remaining_emails) > 0) {
                	$get_existing_users = $this->db->table('people')
						->select('id')
						->whereIn('email_address', $remaining_emails)
						->get();

                    if ($get_existing_users) {
                        foreach ($get_existing_users as $user) {
                            $created_user_ids[] = $user['id'];
                        }
					}
				}
			}
			return $created_user_ids;
		} else {
        	return false;
		}
	}

	protected function addBulkListMembers($user_ids, $list_id) {

        // bulk create list member entries
		$create_list_members = false;
        if (count($user_ids) > 0) {

            $list_members = [];
            foreach ($user_ids as $user_id) {
                $list_members[] = [
                    'user_id' => $user_id,
                    'list_id' => $list_id,
                    'creation_date' => time()
				];
            }

            $create_list_members = $this->db->table('people_list_members')->insert($list_members);
        }

        $remove_tag = $this->db->table('people')
			->where('list_id', $list_id)
			->where('data', 'bulk_import')
			->update(['data'=>'']);

        if ($create_list_members) {
            return true;
        } else {
            return false;
        }
	}

	/**
	 * Sets a user inactive for a given list. If the user is not present on the
	 * list it returns true.
	 *
	 * @param {string} $address -  the email address in question
	 * @param {int} $list_id -     the id of the list
	 * @return bool
	 */
	protected function removeAddress($address,$list_id) {
		$membership_info = $this->getAddressListInfo($address,$list_id);
		if ($membership_info) {
			if ($membership_info['active']) {

				$list_member = PeopleListsMember::find($membership_info['id']);
				$list_member->active = 0;
				$list_member->save();

				if (!$list_member) {
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
			// user marked inactive, webhook removal attempts made
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

            $list_member = PeopleListsMember::findWhere([
            	'user_id'=>$user_id,
				'list_id'=>$list_id
			]);

            $list_member->verification_code = $verification_code;
            $list_member->save();

			if ($list_member) {
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

                $list_member = PeopleListsMember::findWhere([
                    'user_id'=>$user_id,
                    'list_id'=>$list_id,
                    'verification_code'=>$verification_code
                ]);

				if ($list_member) {

					$list_member->verified = 1;
					$list_member->save();

					$api_connection = $this->getConnectionAPI($list_id);
					$rc             = -1;
					if ($api_connection) {
						// connection found, api instantiated
						switch($api_connection['connection_type']) {
							case 'com.mailchimp':
								$mc = $api_connection['api'];
								// TODO: this is currently hardcoded to require a double opt-in
								$rc = $mc->listSubscribe($address, array('double_optin' => false));
								break;
						}
						if (!$rc) {
							// TODO: try again?
						}
					}
					return $list_member->id;
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
			$member = PeopleListsMember::findWhere([
				'user_id' => $user_id,
				'list_id' => $list_id
			]);

			if ($member) {
				$return_array = $member->toArray();
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
	 */
	protected function getUserIDForAddress($address,$with_security_credentials=false) {

	 	$user = People::findWhere([
	 		'email_address' => $address
		]);

		if ($user) {
			if ($with_security_credentials) {
				return $user->toArray();
			} else {
				return $user->id;
			}
		} else {
			return false;
		}
	}

	/**
	 * Returns user id for a given username
	 *
	 * @param {string} $address -  the email address in question
	 * @return id|false
	 */protected function getUserIDForUsername($username) {

	 	$user = People::findWhere([
	 		'username' => trim(strtolower($username))
		]);

		if ($user) {
			return $user->id;
		} else {
			return false;
		}
	}

	protected function validateUserForList($address,$password,$list_id,$browserid_assertion=false,$element_id=null) {
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
			} else {
				return false;
			}
		}
		// we never validated, so automatically return false
		return false;
	}

	/**
	 *
	 * MASS MAILINGS
	 * Email out — requires mass mailing connection
	 *
	 */

	/**
	 * Adds/edits a mailing
	 *
	 * @return bool
	 */
	protected function addMailing($user_id,$list_id,$connection_id,$subject,$template_id=0,$html_content='',$text_content='',$from_name='',$asset=false) {
		// insert

		$mailing = PeopleMailing::create([
            'user_id' => $user_id,
            'list_id' => $list_id,
            'connection_id' => $connection_id,
            'template_id' => $template_id,
            'subject' => $subject,
            'from_name' => $from_name,
            'html_content' => $html_content,
            'text_content' => $text_content,
            'send_date' => 0
        ]);

		// asset metadata
		if ($asset) {
            $this->setMetaData("mailings",$mailing->id,$user_id,"asset_id",$asset);
		}

		if ($mailing) {
			// setup analytics for this mailing
			$analytics = PeopleMailingsAnalytic::create([
                'mailing_id' => $mailing->id,
                'sends' => 0,
                'opens_total' => 0,
                'opens_unique' => 0,
                'opens_mobile' => 0,
                'opens_country' => '{}',
                'opens_ids' => '[]',
                'clicks' => 0,
                'clicks_urls' => '{}',
                'failures' => 0
			]);
		}

		return $mailing->id;
	}

	protected function editMailing($mailing_id,$send_date=false,$subject=false,$html_content=false,$text_content=false,$user_id=false,$from_name=false) {

		if ($user_id) {
            $mailing = PeopleMailing::findWhere(['id'=>$mailing_id, 'user_id'=>$user_id]);
        } else {
            $mailing = PeopleMailing::find($mailing_id);
        }

		$final_edits = array_filter(
			array(
				'subject' => $subject,
				'from_name' => $from_name,
				'html_content' => $html_content,
				'text_content' => $text_content,
				'send_date' => $send_date
			),
            function($value) {
                return CASHSystem::notExplicitFalse($value);
            }
		);

		if ($mailing->update($final_edits)) {
			return true;
		} else {
			return false;
		}
	}

	protected function getMailing($mailing_id,$user_id=false) {

        if ($user_id) {
            $mailing = PeopleMailing::findWhere(['id'=>$mailing_id, 'user_id'=>$user_id]);
        } else {
            $mailing = PeopleMailing::find($mailing_id);
        }

		if ($mailing) {
			return $mailing->toArray();
		} else {
			return false;
		}
	}

	protected function getMailingMetaData($mailing_id, $user_id=false) {
        $result = $this->getMetaData("mailings",$mailing_id,$user_id,"asset_id");

        if ($result) {
        	return $result;
		} else {
        	return false;
		}
	}
	protected function sendMailing($mailing_id,$user_id=false,$asset=false) {
		$mailing = $this->getMailing($mailing_id,$user_id);
		if ($mailing) {
			if ($mailing['send_date'] == 0) {

				$list_details = $this->viewList($mailing['list_id'], true, $mailing['user_id']);

                $merge_vars = [];

                // if there's an asset id we need to look it up and pass for global merge vars
                if ($asset) {
                    // lookup asset details
                    $asset_request = new CASHRequest(
                        array(
                            'cash_request_type' => 'asset',
                            'cash_action' => 'getasset',
                            'id' => $asset,
                            'user_id' => $mailing['user_id']
                        )
                    );

                    if ($asset_request->response['payload']) {

                        $add_code_request = new CASHRequest(
                            array(
                                'cash_request_type' => 'system',
                                'cash_action' => 'addbulklockcodes',
                                'scope_table_alias' => 'mailings',
                                'scope_table_id' => $mailing_id,
								'user_id' => $mailing['user_id'],
								'count' => count($list_details['members'])
                            )
                        );

						if ($add_code_request) {

                            $get_code_request = new CASHRequest(
                                array(
                                    'cash_request_type' => 'system',
                                    'cash_action' => 'getlockcodes',
                                    'scope_table_alias' => 'mailings',
                                    'scope_table_id' => $mailing_id,
                                    'user_id' => $mailing['user_id']
                                )
                            );

                            if (is_array($get_code_request->response['payload'])) {
                                $codes = array_column($get_code_request->response['payload'], 'uid');
							}
						}

                    }
                }

                // build recipient arrays
				if (is_array($list_details)) {
					$recipients = [];
                    $merge_vars = [];
					$success = true;

					if (count($list_details['members']) > 1000) {

                        $recipients_chunked = array_chunk($list_details['members'], 1000);
                        foreach($recipients_chunked as $members) {
                            // reset these every time or you'll get doubles, or worse
                            $recipients = [];
                            $merge_vars = [];
                            foreach ($members as $subscriber) {
                                if ($subscriber['active']) {
                                    if ($subscriber['display_name'] == 'Anonymous' || $subscriber['display_name'] == '') {
                                        $subscriber['display_name'] = $subscriber['email_address'];
                                    }
                                    $recipients[] = array(
                                        'email' => $subscriber['email_address'],
                                        'name' => $subscriber['display_name'],
                                        'type' => 'to',
                                        'metadata' => array(
                                            'user_id' => $subscriber['id']
                                        )
                                    );
                                }

                                // there's a valid asset
                                if ($asset_request->response['payload'] && !empty($codes) && is_array($codes)) {

                                    $code = array_pop($codes);
                                    $merge_vars[] = [
                                        'rcpt' => $subscriber['email_address'],
                                        'vars' => [
                                            [
                                                'name' => 'assetbutton',
                                                'content' => "<a href='".CASH_PUBLIC_URL .
                                                    '/request/html?cash_request_type=system&cash_action=redeemlockcode&list_id=' .
                                                    $mailing['list_id'] .
                                                    "&address=".$subscriber['email_address']."&code=$code&handlequery=1".
                                                    "' class='button'>Download ".
                                                    htmlentities($asset_request->response['payload']['title']).'</a>'
                                            ]
                                        ]
                                    ];
                                }
                            }

                            if (CASHSystem::sendMassEmail(
                                $user_id,
                                $mailing['subject'],
                                $recipients,
                                $mailing['html_content'], // message body
                                $mailing['subject'], // message subject
                                [],
                                $merge_vars, // local merge vars (per email)
                                false,
                                true,
                                true,
                                $mailing
                            )) {

                                $this->editMailing($mailing_id,time());
                                $this->addToMailingAnalytics($mailing_id,count($recipients));

                            } else {
                                $success = false;
                            }

                        }

                        return $success;

                    } else {
                        foreach ($list_details['members'] as $subscriber) {
                            if ($subscriber['active']) {
                                if ($subscriber['display_name'] == 'Anonymous' || $subscriber['display_name'] == '') {
                                    $subscriber['display_name'] = $subscriber['email_address'];
                                }
                                $recipients[] = array(
                                    'email' => $subscriber['email_address'],
                                    'name' => $subscriber['display_name'],
                                    'type' => 'to',
                                    'metadata' => array(
                                        'user_id' => $subscriber['id']
                                    )
                                );
                            }

                            // there's a valid asset
                            if ($asset_request->response['payload'] && !empty($codes) && is_array($codes)) {

                                $code = array_pop($codes);
                                $merge_vars[] = [
                                    'rcpt' => $subscriber['email_address'],
                                    'vars' => [
                                        [
                                            'name' => 'assetbutton',
                                            'content' => "<a href='".CASH_PUBLIC_URL .
                                                '/request/html?cash_request_type=system&cash_action=redeemlockcode&list_id=' .
                                                $mailing['list_id'] .
                                                "&address=".$subscriber['email_address']."&code=$code&handlequery=1".
                                                "' class='button'>Download ".
                                                htmlentities($asset_request->response['payload']['title']).'</a>'
                                        ]
                                    ]
                                ];
                            }
                        }

                        if (CASHSystem::sendMassEmail(
                            $user_id,
                            $mailing['subject'],
                            $recipients,
                            $mailing['html_content'], // message body
                            $mailing['subject'], // message subject
                            [],
                            $merge_vars, // local merge vars (per email)
                            false,
                            true,
                            true,
                            $mailing
                        )) {

                            $this->editMailing($mailing_id,time());
                            $this->addToMailingAnalytics($mailing_id,count($recipients));

                            return true;

                        } else {
                            return false;
                        }
                    }
				}
			}
		}
		return false; // no return this far? return false
	}

	protected function getMailingAnalytics($mailing_id,$user_id=false) {

        try {
            if ($user_id) {
                $analytic = PeopleMailingsAnalytic::findWhere(['id'=>$mailing_id, 'user_id'=>$user_id]);
            } else {
                $analytic = PeopleMailingsAnalytic::find($mailing_id);
            }

        } catch (\Exception $e) {
            if (CASH_DEBUG) {
                CASHSystem::errorLog($e->getMessage());
            }
            return false;
        }

		if ($analytic) {
			return $analytic->toArray();
		} else {
			return false;
		}
	}

	protected function addToMailingAnalytics(
		$mailing_id,
		$sends=0,
		$opens_total=0,
		$opens_mobile=0,
		$opens_country=false,
		$opens_id=false,
		$click_url=false,
		$failures=0,
		$user_id=false
	) {
		$analytics = $this->getMailingAnalytics($mailing_id,$user_id);
		if ($analytics) {
			$analytics['sends'] += $sends;
			$analytics['opens_total'] += $opens_total;
			$analytics['opens_mobile'] += $opens_mobile;
			$analytics['failures'] += $failures;
			if ($opens_total && $opens_id) {
				$current_opens_ids = json_decode($analytics['opens_ids'],true);
				if (!in_array($opens_id, $current_opens_ids)) {
					$analytics['opens_unique']++;
					$current_opens_ids[] = $opens_id;
					$analytics['opens_ids'] = json_encode($current_opens_ids);
				}
			}
			if (is_array($opens_country)) {
				$current_opens_country = json_decode($analytics['opens_country'],true);
				foreach ($opens_country as $country => $details) {
					if (array_key_exists($country, $current_opens_country)) {
						$current_opens_country[$country]['total']++;
						$keys = array_keys($details['regions']);
						if (array_key_exists($keys[0], $current_opens_country[$country]['regions'])) {
							$current_opens_country[$country]['regions'][$keys[0]]++;
						} else {
							$current_opens_country[$country]['regions'][$keys[0]] = 1;
						}
						$keys = array_keys($details['cities']);
						if (array_key_exists($keys[0], $current_opens_country[$country]['cities'])) {
							$current_opens_country[$country]['cities'][$keys[0]]++;
						} else {
							$current_opens_country[$country]['cities'][$keys[0]] = 1;
						}
						$keys = array_keys($details['postal']);
						if (array_key_exists($keys[0], $current_opens_country[$country]['postal'])) {
							$current_opens_country[$country]['postal'][$keys[0]]++;
						} else {
							$current_opens_country[$country]['postal'][$keys[0]] = 1;
						}
					} else {
						$current_opens_country[$country] = $details;
						$current_opens_country[$country]['total'] = 1;
					}
				}
				$analytics['opens_country'] = json_encode($current_opens_country);
			}
			if ($click_url) {
				$current_clicks_urls = json_decode($analytics['clicks_urls'],true);
				if (array_key_exists($click_url, $current_clicks_urls)) {
					$current_clicks_urls[$click_url]++;
				} else {
					$current_clicks_urls[$click_url] = 1;
				}
				$analytics['clicks']++;
				$analytics['clicks_urls'] = json_encode($current_clicks_urls);
			}

			$analytic = PeopleMailingsAnalytic::find($analytics['id']);

			$analytic->update([
                'sends' => $analytics['sends'],
                'opens_total' => $analytics['opens_total'],
                'opens_unique' => $analytics['opens_unique'],
                'opens_mobile' => $analytics['opens_mobile'],
                'opens_country' => $analytics['opens_country'],
                'opens_ids' => $analytics['opens_ids'],
                'clicks' => $analytics['clicks'],
                'clicks_urls' => $analytics['clicks_urls'],
                'failures' => $analytics['failures']
			]);

			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * WEBHOOKS
	 * Handle incoming webhooks
	 *
	 */

	/**
	 * Used with the verbose API for remote webhook calls — incoming data into the system from
	 * third parties, etc.
	 *
	 */protected function processWebhook($origin,$user_id,$list_id=0,$type=false,$data=false,$mandrill_events=false) {
		switch ($origin) {
			case 'com.mailchimp':
				// make sure the API key matches the user_id of the list owner
				$ownership = $this->verifyListOwner($user_id,$list_id);
				if (!$ownership) {
					return false;
				}
				// matches. go:
				$mailchimp_type = $type;
				$mailchimp_details = $data;
				if ($mailchimp_type == 'subscribe') {
					$user_name = 'Anonymous';
					if (!empty($mailchimp_details['merges']['FNAME'])) {
						$user_name = $mailchimp_details['merges']['FNAME'] . ' ' . $mailchimp_details['merges']['LNAME'];
					}
					$mailchimp_details['source'] = 'com.mailchimp';
					$result = $this->addAddress(
						$mailchimp_details['email'],
						$list_id,
						1, // verified. trust all users from mailchimp
						'', // no initial comment
						json_encode($mailchimp_details), // might as well store where it came from
						$user_name, // this is the display name we put together up there a bit,
						false, // don't need to verify
						true // tell the function that the add came from the service, no verification needed
					);
					return $result;
				} else if ($mailchimp_type == 'unsubscribe' || $mailchimp_type == 'cleaned') {
					// move user from active to inactive
					$result = $this->removeAddress($mailchimp_details['email'],$list_id);
					return $result;
				} else if ($mailchimp_type ==  'upemail') {
					// update email address with data in $mailchimp_details
					// this is a do-later bit...editing a users email address...
				}
				break;
			case 'com.mandrillapp':
				if (!$mandrill_events) {
					return false;
				}
				//error_log($mandrill_events);
				$mandrill_events = json_decode(stripslashes($mandrill_events),true);
				foreach ($mandrill_events as $mandrill_event) {
					if (isset($mandrill_event['msg']['metadata']['mailing_id'])) {
						$mailing_id = $mandrill_event['msg']['metadata']['mailing_id'];
					} else {
						$mailing_id = false;
					}
					$mailing = $this->getMailing($mailing_id);
					if ($mailing['user_id'] != $user_id) {
						return false; // incorrect owner
					}
					// possible events: 'hard_bounce','soft_bounce','open','click','spam','unsub','reject'
					if ($mandrill_event['event'] == 'hard_bounce' ||
						$mandrill_event['event'] == 'soft_bounce' ||
						$mandrill_event['event'] == 'spam' ||
						$mandrill_event['event'] == 'unsub' ||
						$mandrill_event['event'] == 'reject'
					) {
						if ($mandrill_event['event'] != 'soft_bounce') {
							// soft bounce could be a temporary error, so don't remove the person from the list
							// but otherwise adios:
							$this->removeAddress($mandrill_event['msg']['email'],$mailing['list_id']);
						}
						// mark as failed
						$this->addToMailingAnalytics($mailing_id,0,0,0,false,false,false,1);
					} else if ($mandrill_event['event'] == 'open') {
						$mobile = 0;
						$country_info = false;
						if (isset($mandrill_event['msg']['user_agent_parsed'])) {
							if ($mandrill_event['msg']['user_agent_parsed']['mobile']) {
								$mobile = 1;
							}
						}
						if (isset($mandrill_event['location'])) {
							$country_info = array(
								$mandrill_event['location']['country_short'] => array(
									'regions' => array(
										$mandrill_event['location']['region'] => 1
									),
									'cities' => array(
										$mandrill_event['location']['city'] => 1
									),
									'postal' => array(
										$mandrill_event['location']['postal_code'] => 1
									)
								)
							);
						}
						$this->addToMailingAnalytics($mailing_id,0,1,$mobile,$country_info,$mandrill_event['msg']['metadata']['user_id']);
					} else if ($mandrill_event['event'] == 'click') {
						$this->addToMailingAnalytics($mailing_id,0,0,0,false,false,$mandrill_event['url']);
					}
				}
				break;
			default:
				return false;
		}
	}

	protected function buildMailingContent($template_id, $html_content, $title, $subject, $template="user_email", $asset=false) {

		// use default template
		if ($template_id == 'default') {
			// parse the html content for any markdown
			$html_content = CASHSystem::parseMarkdown($html_content);

			if ($template = CASHSystem::setMustacheTemplate($template)) {

				if ($asset) {
					$template = str_replace('$ASSET$', '*|ASSETBUTTON|*', $template);
				} else {
					$template = str_replace('$ASSET$', '', $template);
				}

				// render the mustache template and return
				$html_content = CASHSystem::renderMustache(
					$template, array(
						// array of values to be passed to the mustache template
						'encoded_html' => $html_content,
						'message_title' => $title,
						'subject' => $subject,
						'cdn_url' => (defined('CDN_URL')) ? CDN_URL : CASH_ADMIN_URL,
						'unsubscribe' => 'https://cashmusic.org'
					)
				);
			}

			return $html_content;

			// no template, just use the HTML
		} else if ($template_id == 'none') {
			return $html_content;
		}

		// fallback if nothing was selected
		return CASHSystem::parseMarkdown($html_content);
	}
} // END class
?>
