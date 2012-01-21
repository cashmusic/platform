<?php
/**
 * SystemPlant deals with any low-level or secure requests that need processing. 
 * Some things like user logins appear here instead of their more natural homes 
 * in order to centralize potential security risks.
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
class SystemPlant extends PlantBase {
	
	public function __construct($request_type,$request) {
		$this->request_type = 'system';
		$this->plantPrep($request_type,$request);
		// get global salt for hashing
		$global_settings = parse_ini_file(CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php');
		$this->salt = $global_settings['salt'];
	}
	
	public function processRequest() {
		if ($this->action) {
			$this->routing_table = array(
				// alphabetical for ease of reading
				// first value  = target method to call
				// second value = allowed request methods (string or array of strings)
				'addlogin'                => array('addLogin','direct'),
				'getapicredentials'       => array('getAPICredentials','direct'),
				'setapicredentials'       => array('setAPICredentials','direct'),
				'setlogincredentials'     => array('setLoginCredentials','direct'),
				'validateapicredentials'  => array('validateAPICredentials','direct'),
				'validatelogin'           => array('validateLogin','direct')
			);
			// see if the action matches the routing table:
			$basic_routing = $this->routeBasicRequest();
			if ($basic_routing !== false) {
				return $basic_routing;
			} else {
				// switch statement for cases that require more thinking than a straight pass-throgh
				switch ($this->action) {
					case 'fitmk':
						// for Duke...you're awesome.
						$litany = "I must not fear. Fear is the mind-killer. "
								 . "Fear is the little-death that brings total obliteration. "
								 . "I will face my fear. I will permit it to pass over me and through me. "
								 . "And when it has gone past I will turn the inner eye to see its path. "
								 . "Where the fear has gone there will be nothing. Only I will remain. -- Frank Herbert";
						return $this->pushSuccess($litany,'check it');
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
				$this->request,
				'no action specified'
			);
		}
	}
	
	/**
	 * Generates a password hash and compares against the stored hash
	 *
	 * @param {string} $address -  the email address in question
	 * @param {string} $password - the password
	 * @return array|false
	 */protected function validateLogin($address,$password,$require_admin=false,$verified_address=false,$browserid_assertion=false,$element_id=null) {
		$login_method = 'internal';
		if ($verified_address && !$address) {
			// claiming verified without an address? false!
			return false;
		} else if ((!$address && !$browserid_assertion) && (!$address && !$password)) {
			// none of the fancy stuff but you're trying to push through no user/pass? bullshit! false!
			return false;
		}
		if (!$password) {
			// set a password string for hashing
			$password = 'password'; // ha! i just made someone doing a security review really sad.
		}
		$password_hash = hash_hmac('sha256', $password, $this->salt);
		if ($browserid_assertion && !$verified_address) {
			$address = CASHSystem::getBrowserIdStatus($browserid_assertion);
			if (!$address) {
				return false;
			} else {
				$verified_address = true;
				$login_method = 'browserid';				
			}
		}
		if ($browserid_assertion && $verified_address) {
			$login_method = 'browserid';
		}
		$result = $this->db->getData(
			'users',
			'id,password,is_admin',
			array(
				"email_address" => array(
					"condition" => "=",
					"value" => $address
				)
			)
		);
		if ($password_hash == $result[0]['password'] || $verified_address) {
			if (($require_admin && $result[0]['is_admin']) || !$require_admin) {
				$this->recordLoginAnalytics($result[0]['id'],$element_id,$login_method);
				return $result[0]['id'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Records the basic login data to the people analytics table
	 *
	 * @return boolean
	 */protected function recordLoginAnalytics($user_id,$element_id=null,$login_method='internal') {
		$ip_and_proxy = CASHSystem::getRemoteIP();
		$result = $this->db->setData(
			'people_analytics',
			array(
				'user_id' => $user_id,
				'element_id' => $element_id,
				'access_time' => time(),
				'client_ip' => $ip_and_proxy['ip'],
				'client_proxy' => $ip_and_proxy['proxy'],
				'login_method' => $login_method
			)
		);
		return $result;
	}

	/**
	 * Adds a new user to the system, setting login details
	 *
	 * @param {string} $address -  the email address in question
	 * @param {string} $password - the password
	 * @return array|false
	 */protected function addLogin($address,$password,$display_name='Anonymous',$first_name='',$last_name='',$organization='',$is_admin=0) {
		$password_hash = hash_hmac('sha256', $password, $this->salt);
		$result = $this->db->setData(
			'users',
			array(
				'email_address' => $address,
				'password' => $password_hash,
				'display_name' => $display_name,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'organization' => $organization,
				'is_admin' => $is_admin
			)
		);
		return $result;
	}

	/**
	 * Resets email/password credentials for a user
	 *
	 * @param {int} $user_id -  the user
	 * @return array|false
	 */protected function setLoginCredentials($user_id,$address,$password) {
		$password_hash = hash_hmac('sha256', $password, $this->salt);
		$credentials = array(
			'email_address' => $address,
			'password' => $password_hash
		);
		$result = $this->db->setData(
			'users',
			$credentials,
			array(
				"id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			)
		);
		return $result;
	}

	/**
	 * Sets or resets API credentials for a user
	 *
	 * @param {int} $user_id -  the user
	 * @return array|false
	 */protected function setAPICredentials($user_id) {
		$some_shit = time() . $user_id . rand(976654,1234567267);
		$api_key = hash_hmac('md5', $some_shit, $this->salt) . substr((string) time(),6);
		$api_secret = hash_hmac('sha256', $some_shit, $this->salt);
		$credentials = array(
			'api_key' => $api_key,
			'api_secret' => $api_secret
		);
		$result = $this->db->setData(
			'users',
			$credentials,
			array(
				"id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			)
		);
		if ($result) {
			return $credentials;
		} else {
			return false;
		}
	}

	/**
	 * Gets API credentials for a user id
	 *
	 * @param {int} $user_id -  the user
	 * @return array|false
	 */protected function getAPICredentials($user_id) {
		$user = $this->db->getData(
			'users',
			'api_key,api_secret',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			)
		);
		if ($user) {
			return array(
				'api_key' => $user[0]['api_key'],
				'api_secret' => $user[0]['api_secret']
			);
		} else {
			return false;
		}
	}

	/**
	 * Verifies API credentials and returns authorization type (api_key || api_fullauth || none) and user_id
	 *
	 * @param {int} $user_id -  the user
	 * @return array|false
	 */protected function validateAPICredentials($api_key,$api_secret=false) {
		$user_id = false;
		$auth_type = 'none';
		if (!$api_secret) {
			$auth_type = 'api_key';
			$user = $this->db->getData(
				'users',
				'id',
				array(
					"api_key" => array(
						"condition" => "=",
						"value" => $api_key
					)
				)
			);
			if ($user) {
				$user_id = $user[0]['id'];
			}
		}
		if ($user_id) {
			return array(
				'auth_type' => $auth_type,
				'user_id' => $user_id
			);
		} else {
			return false;
		}
	}

} // END class 
?>