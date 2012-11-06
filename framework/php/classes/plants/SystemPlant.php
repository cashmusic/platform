<?php
/**
 * SystemPlant deals with any low-level or secure requests that need processing. 
 * Some things like user logins appear here instead of their more natural homes 
 * in order to centralize potential security risks.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class SystemPlant extends PlantBase {
	// hard-coded to avoid 0/o, l/1 type confusions on download cards
	protected $lock_code_chars = array(
		'all_chars' => array('2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z'),
		'code_break' => array(2,3,3,4,4,4,5)
	);
	
	public function __construct($request_type,$request) {
		$this->request_type = 'system';
		$this->routing_table = array(
				// alphabetical for ease of reading
				// first value  = target method to call
				// second value = allowed request methods (string or array of strings)
				'addlogin'                => array('addLogin','direct'),
				'addlockcode'             => array('addLockCode','direct'),
				'deletesettings'          => array('deleteSettings','direct'),
				'getapicredentials'       => array('getAPICredentials','direct'),
				'getlockcodes'            => array('getLockCodes','direct'),
				'getsettings'             => array('getSettings','direct'),
				'migratedb'               => array('doMigrateDB','direct'),
				'redeemlockcode'          => array('redeemLockCode',array('direct','get','post')),
				'setapicredentials'       => array('setAPICredentials','direct'),
				'setlogincredentials'     => array('setLoginCredentials','direct'),
				'setresetflag'            => array('setResetFlag','direct'),
				'setsettings'             => array('setSettings','direct'),
				'validateapicredentials'  => array('validateAPICredentials','direct'),
				'validatelogin'           => array('validateLogin','direct'),
				'validateresetflag'       => array('validateResetFlag',array('direct','get','post'))
			);
		// get global salt for hashing
		$this->salt = CASHSystem::getSystemSettings('salt');
		$this->plantPrep($request_type,$request);
	}
	
	/**
	 * Wrapper for CASHData migrateDB call. Currently used for SQLite -> MySQL migrations but any 
	 * from/to should be possible. More tests need to be written for full support.
	 *
	 * @return bool
	 */
	protected function doMigrateDB($todriver,$tosettings) {
		return $this->db->migrateDB($todriver,$tosettings);
	}

	protected function getCryptConstants() {
		if (!defined('CRYPT_BLOWFISH')) define('CRYPT_BLOWFISH', 0);
		if (!defined('CRYPT_SHA512')) define('CRYPT_SHA512', 0);
		if (!defined('CRYPT_SHA256')) define('CRYPT_SHA256', 0);

		return CRYPT_BLOWFISH + CRYPT_SHA512 + CRYPT_SHA256;
	}

	protected function generatePasswordHash($password,$force52compatibility=false) {
		$password_hash = false;

		$ciphers = $this->getCryptConstants();

		if ($ciphers && !$force52compatibility) {
			if (CRYPT_BLOWFISH == 1) {
				$password_hash = crypt(md5($password . $this->salt), '$2a$13$' . md5(time() . $this->salt) . '$');
			} else if (CRYPT_SHA512 == 1) {
				$password_hash = crypt(md5($password . $this->salt), '$6$rounds=6666$' . md5(time() . $this->salt) . '$');
			} else if (CRYPT_SHA256 == 1) {
				$password_hash = crypt(md5($password . $this->salt), '$5$rounds=6666$' . md5(time() . $this->salt) . '$');
			}
		} else {
			$key = time();
			$password_hash = $key . '$' . hash_hmac('sha256', md5($password . $this->salt), $key);
		}

		return $password_hash;
	}
	
	/**
	 * Logins are validated using the email address given with a salted sha256 hash of the given 
	 * password. Blowfish is unavailable to PHP 5.2 (reliably) so we're limited in hashing. The 
	 * system salt is stored in /framework/settings/cashmusic.ini.php outside the database for 
	 * additional security.
	 * 
	 * In addition to the standard email/pass we also validate against Mozilla's Browser ID standard
	 * using the browserid_assetion which can be passed in. This works with the CASHSystem Browser ID 
	 * calls to determine a positive login status for the user, get the email address, and compare it 
	 * to the system to return the correct user and login status. 
	 *
	 * Pass require_admin to only return true for admin-level users. Pass an element_id if you want 
	 * the login analytics to be tied to a specific element. 
	 *
	 * @return array|false
	 */protected function validateLogin($address,$password,$require_admin=false,$verified_address=false,$browserid_assertion=false,$element_id=null) {
		$this->sessionClearAll();
		$login_method = 'internal';
		if ($verified_address && !$address) {
			// claiming verified without an address? false!
			return false;
		} else if ((!$address && !$browserid_assertion) && (!$address && !$password)) {
			// none of the fancy stuff but you're trying to push through no user/pass? bullshit! false!
			return false;
		}
		if (!$password && !$browserid_assertion) {
			return false; // seriously no password? lame.
		}

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
		
		if ($result) {
			$ciphers = $this->getCryptConstants();
			$parts = explode('$', $result[0]['password']);
			if ($ciphers || count($parts) > 2) {
				$password_hash = crypt(md5($password . $this->salt), $result[0]['password']);
			} else {
				$key = $parts[0];
				$password_hash = $key . '$' . hash_hmac('sha256', md5($password . $this->salt), $key);
			}
		}

		if ($result && ($result[0]['password'] == $password_hash || $verified_address)) {
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
	 */protected function addLogin($address,$password,$is_admin=0,$display_name='Anonymous',$first_name='',$last_name='',$organization='',$address_country='',$force52compatibility=false) {
		$password_hash = $this->generatePasswordHash($password,$force52compatibility);

		$result = $this->db->setData(
			'users',
			array(
				'email_address' => $address,
				'password' => $password_hash,
				'display_name' => $display_name,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'organization' => $organization,
				'address_country' => $address_country,
				'is_admin' => $is_admin
			)
		);
		if ($result && $is_admin) {
			$this->setAPICredentials($result);
		}
		return $result;
	}

	/**
	 * Resets email/password credentials for a user
	 *
	 * @param {int} $user_id -  the user
	 * @return array|false
	 */protected function setLoginCredentials($user_id,$address,$password) {
		$password_hash = $this->generatePasswordHash($password);

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
	 * Sets or resets the password reset for a user
	 *
	 * @return key(md5 hash)|false
	 */protected function setResetFlag($address) {
		$user_id = $this->db->getData(
			'users',
			'id',
			array(
				"email_address" => array(
					"condition" => "=",
					"value" => $address
				)
			)
		);
		if ($user_id) {
			$user_id = $user_id[0]['id'];
			// first remove any password resets for the same user
			$this->db->deleteData(
				'people_resetpassword',
				array(
					'user_id' => array(
						'condition' => '=',
						'value' => $user_id
					)
				)
			);
			$key = md5($user_id . rand(976654,1234567267));
			$result = $this->db->setData(
				'people_resetpassword',
				array(
					'user_id' => $user_id,
					'key' => $key
				)
			);
			if ($result) {
				return $key;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Verifies that the password reset is valid 
	 *
	 * @return bool
	 */protected function validateResetFlag($address,$key) {
		$user_id = $this->db->getData(
			'users',
			'id',
			array(
				"email_address" => array(
					"condition" => "=",
					"value" => $address
				)
			)
		);
		if ($user_id) {
			$user_id = $user_id[0]['id'];
			$result = $this->db->getData(
				'people_resetpassword',
				'creation_date',
				array(
					"user_id" => array(
						"condition" => "=",
						"value" => $user_id
					),
					"key" => array(
						"condition" => "=",
						"value" => $key
					)
				)
			);
			if ($result) {
				if (($result[0]['creation_date'] + 86400) > time()) {
					return true;
				} else {
					// request expired. boo.
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
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
		} else {
			$auth_type = 'api_fullauth';
			$user = $this->db->getData(
				'users',
				'id',
				array(
					"api_key" => array(
						"condition" => "=",
						"value" => $api_key
					),
					"api_secret" => array(
						"condition" => "=",
						"value" => $api_secret
					),
				)
			);
		}
		if ($user) {
			$user_id = $user[0]['id'];
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

	/**
	 * Removes system settings of the given type for a user — be careful with wild cards. (Don't
	 * use them unless you want to delete all system settings for a user. So, you know, don't.)
	 *
	 * @return bool
	 */
	protected function deleteSettings($user_id,$type) {
		$result = $this->db->deleteData(
			'settings',
			array(
				"type" => array(
					"condition" => "=",
					"value" => $type
				),
				"user_id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			)
		);
		return $result;
	}

	/**
	 * Gets settings of the given type for a user. Set return_json to true and the system will 
	 * return the stored JSON without decoding.
	 *
	 * @return string|array|false
	 */
	protected function getSettings($user_id,$type,$return_json=false) {
		$result = $this->db->getData(
			'settings',
			'*',
			array(
				"type" => array(
					"condition" => "=",
					"value" => $type
				),
				"user_id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			)
		);
		if ($result) {
			if ($return_json) {
				return $result[0];
			} else {
				return json_decode($result[0]['value'],true);
			}
		} else {
			return false;
		}
	}

	/**
	 * Sets data for the given type for a user. This is basically a single key/value, so if the type 
	 * already exists this call with overwrite the existing value.
	 *
	 * @return bool
	 */
	protected function setSettings($user_id,$type,$value) {
		$go = true;
		$condition = false;
		// first check to see if the user/key combo exists.
		// a little inelegant, but necessary for a key/value store
		$exists = $this->db->getData(
			'settings',
			'id,value',
			array(
				"type" => array(
					"condition" => "=",
					"value" => $type
				),
				"user_id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			)
		);
		if ($exists) {
			// the key/user exists, so first compare value
			if ($exists[0]['value'] == $value) {
				// equal to what's there already? do nothing, return true
				$go = false;
			} else {
				// different? set conditions to perform an update
				$condition = array(
					"id" => array(
						"condition" => "=",
						"value" => $exists[0]['id']
					)
				);
			}
		} 
		if ($go) {
			// insert/update
			$result = $this->db->setData(
				'settings',
				array(
					'user_id' => $user_id,
					'type' => $type,
					'value' => json_encode($value)
				),
				$condition
			);
			return $result;
		} else {
			// we're already up to date...do nothing but signal 'okay'
			return true;
		}
	}
	
	
	
	
	
	
	
	/*
	 *
	 * Here lie a bunch of lock code functions that need to reference elements
	 * instead of assets. duh.
	 *
	 */
	
	/**
	 * Retrieves the last known UID or if none are found creates and returns a 
	 * random UID as a starting point
	 *
	 * @return string
	 */protected function getLastLockCode() {
		$result = $this->db->getData(
			'lock_codes',
			'uid',
			false,
			1,
			'id DESC'
		);
		if ($result) {
			$code = $result[0]['uid'];
		} else {
			$code = false;
		}
		return $code;
	}

	/**
	 * Creates a new lock/unlock code for and asset
	 *
	 * @param {integer} $element_id - the element for which you're adding the lock code
	 * @return string|false
	 */protected function addLockCode($scope_table_alias,$scope_table_id,$user_id=0){
		$code = $this->generateCode(
			$this->lock_code_chars['all_chars'],
			$this->lock_code_chars['code_break'],
			$this->getLastLockCode()
		);
		$result = $this->db->setData(
			'lock_codes',
			array(
				'uid' => $code,
				'scope_table_alias' => $scope_table_alias,
				'scope_table_id' => $scope_table_id,
				'user_id' => $user_id
			)
		);
		if ($result) { 
			return $code;
		} else {
			return false;
		}
	}

	/**
	 * Attempts to redeem a given lock code, returning all details for the code on success or false 
	 * on failure. The code is tied to a scope_table_alias and scope_table_id pointing to a specific 
	 * asset, element, etc. 
	 *
	 * Pass a specific scope_table_alias, scope_table_id, or user_id to limit results to only matching 
	 * returns. 
	 * 
	 * This will continue to return true for four hours after initial redemption — in the case of a 
	 * failed download this will give a user a second try without risking any long-term breach.
	 *
	 * @return array|false
	 */
	protected function redeemLockCode($code,$scope_table_alias=false,$scope_table_id=false,$user_id=false) {
		$code_details = $this->getLockCode($code);
		if ($code_details) {
			// check against optional arguments — if they're found then make sure they match
			// the data stored with the code...if not invalidate the request and return false
			$proceed = true;
			if ($scope_table_alias && ($scope_table_alias != $code_details['scope_table_alias'])) {
				$proceed = false;
			}
			if ($scope_table_id && ($scope_table_id != $code_details['scope_table_id'])) {
				$proceed = false;
			}
			if ($user_id && ($user_id != $code_details['user_id'])) {
				$proceed = false;
			}
			if ($proceed) {
				// details found
				if (!$code_details['claim_date']) {
					$result = $this->db->setData(
						'lock_codes',
						array(
							'claim_date' => time()
						),
						array(
							"id" => array(
								"condition" => "=",
								"value" => $code_details['id']
							)
						)
					);
					if ($result) {
						return $code_details;
					} else {
						return false;
					}
				} else {
					// allow retries for four hours after claim
					if (($code_details['claim_date'] + 14400) > time()) {
						return $code_details;
					} else {
						return false;
					}
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Returns all data for a given code. Look for "scope_table_alias" and "scope_table_id" in the 
	 * returned aray to find the asset / element / etc that was unlocked with the code.
	 *
	 * @return array|false
	 */
	protected function getLockCode($code) {
		$result = $this->db->getData(
			'lock_codes',
			'*',
			array(
				"uid" => array(
					"condition" => "=",
					"value" => $code
				)
			),
			1
		);
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}

	/**
	 * Gets all lock codes for a given resource.
	 *
	 * @return array|false
	 */
	protected function getLockCodes($scope_table_alias,$scope_table_id) {
		$result = $this->db->getData(
			'lock_codes',
			'*',
			array(
				"scope_table_alias" => array(
					"condition" => "=",
					"value" => $scope_table_alias
				),
				"scope_table_id" => array(
					"condition" => "=",
					"value" => $scope_table_id
				)
			)
		);
		return $result;
	}

	protected function consistentShuffle(&$items, $seed=false) {
		// original here: http://www.php.net/manual/en/function.shuffle.php#105931
		$original = md5(serialize($items));
		mt_srand(crc32(($seed) ? $seed : $items[0]));
		for ($i = count($items) - 1; $i > 0; $i--){
			$j = @mt_rand(0, $i);
			list($items[$i], $items[$j]) = array($items[$j], $items[$i]);
		}
		if ($original == md5(serialize($items))) {
			list($items[count($items) - 1], $items[0]) = array($items[0], $items[count($items) - 1]);
		}
	}
	
	protected function generateCode($all_chars,$code_break,$last_code=false) {
		$this->consistentShuffle($all_chars,$this->salt);
		$this->consistentShuffle($code_break,$this->salt);
		if (!$last_code) {
			$last_code = '';
			for ($i = 1; $i <= 10; $i++) {
				$last_code .= $all_chars[rand(0,count($all_chars) - 1)];
			}
		}
		$sequential = substr($last_code,1,$code_break[0])
					. substr($last_code,0 - (7 - $code_break[0]));
		$sequential = $this->iterateChars($sequential,$all_chars);
		$new_code = $all_chars[rand(0,count($all_chars) - 1)]
		 		  . substr($sequential,0,$code_break[0])
				  . $all_chars[rand(0,count($all_chars) - 1)]
				  . $all_chars[rand(0,count($all_chars) - 1)]
				  . substr($sequential,0 - (7 - $code_break[0]));
		return $new_code;
	}

	protected function iterateChars($chars,$all_chars) {
		$chars = str_split($chars);
		// start with the last character of the $chars string
		$current_char = count($chars) - 1;
		$loop = 1;
		do {
			$loop--;
			$current_key = array_search($chars[$current_char],$all_chars);
			if ($current_key == count($all_chars) - 1) {
				$loop++;
				$chars[$current_char] = $all_chars[0];
				if ($current_char == 0) {
					$current_char = count($chars) - 1;
				} else {
					$current_char--;
				}
			} else {
				$chars[$current_char] = $all_chars[$current_key + 1];
			}
		} while ($loop > 0);
		$chars = implode($chars);
		return $chars;
	}
	
} // END class 
?>