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
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 * This file is generously sponsored by Howard Lull Music www.turningeyes.com Keep the music coming!
 *
 **/

namespace CASHMusic\Plants\System;

use CASHMusic\Core\PlantBase;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Entities\People;
use CASHMusic\Entities\PeopleAnalytic;
use CASHMusic\Entities\PeopleAnalyticsBasic;
use CASHMusic\Entities\PeopleResetPassword;
use CASHMusic\Entities\SystemLockCode;
use CASHMusic\Entities\SystemSettings;
use CASHMusic\Entities\SystemTemplate;
use Pixie\Exception;


class SystemPlant extends PlantBase {
	// hard-coded to avoid 0/o, l/1 type confusions on download cards
	protected $lock_code_chars = array(
		'all_chars' => array('2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z'),
		'code_break' => array(2,3,3,4,4,4,5)
	);

	public function __construct($request_type,$request,$pdo) {
		$this->request_type = 'system';
		$this->pdo = $pdo;
        $this->getRoutingTable();

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
	 */protected function validateLogin($address,$password,$require_admin=false,$verified_address=false,$browserid_assertion=false,$element_id=null,$keep_session=false) {
	 	$address = trim($address);
	 	$password = trim($password);

		if (!$keep_session) {
			$this->sessionClearAll();
		}
		$login_method = 'internal';
		if ($verified_address && !$address) {
			// claiming verified without an address? false!
			return false;
		} else if ((!$address) && (!$address && !$password)) {
			// none of the fancy stuff but you're trying to push through no user/pass? bullshit! false!
			return false;
		}
		if (!$password) {
			return false; // seriously no password? lame.
		}

		$user_result = $this->orm->findWhere(People::class, ['email_address'=>$address]);

		if ($user_result) {
			$ciphers = $this->getCryptConstants();
			$parts = explode('$', $user_result->password);
			if ($ciphers || count($parts) > 2) {
				$password_hash = crypt(md5($password . $this->salt), $user_result->password);
			} else {
				$key = $parts[0];
				$password_hash = $key . '$' . hash_hmac('sha256', md5($password . $this->salt), $key);
			}
		}

		if ($user_result && ($user_result->password == $password_hash || $verified_address)) {
			if (($require_admin && $user_result->is_admin) || !$require_admin) {
				$this->recordLoginAnalytics($user_result->id,$element_id,$login_method);
				return $user_result->id;
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
	 */
	protected function recordLoginAnalytics($user_id,$element_id=null,$login_method='internal') {
		$result = false;

		// check settings first as they're already loaded in the environment
		$record_type = CASHSystem::getSystemSettings('analytics');
		if ($record_type == 'off') {
			return true;
		}

		// first the big record if needed
		if ($record_type == 'full' || !$record_type) {
			$ip_and_proxy = CASHSystem::getRemoteIP();
			$result = $this->orm->create(PeopleAnalytic::class, [
                'user_id' => $user_id,
                'element_id' => $element_id,
                'access_time' => time(),
                'client_ip' => $ip_and_proxy['ip'],
                'client_proxy' => $ip_and_proxy['proxy'],
                'login_method' => $login_method
			]);

			error_log('create people analytic');

		}
		// basic logging happens for full or basic
		if ($record_type == 'full' || $record_type == 'basic') {
                if ($people_analytics_basic = $this->orm->findWhere(PeopleAnalyticsBasic::class,
					['user_id' => $user_id])) {
                    $last_login = $people_analytics_basic->modification_date;
                    $new_total = $people_analytics_basic->total + 1;
                } else {
                    $last_login = time();
                    $new_total = 1;
                }

                // store the "last_login" time
                if ($login_method == 'internal') {
                	try {
                        $user = $this->orm->find(People::class, $user_id);
                        $user->data = array_merge($user->data, ['last_login'=>$last_login]);
                        $user->save();

					} catch (Exception $e) {
                		CASHSystem::errorLog($e->getMessage());
					}

                    if (!$people_analytics_basic) {

                        try {
                		$result = $this->orm->create(PeopleAnalyticsBasic::class, [
                            'total'=>$new_total,
                            'user_id'=>$user_id
                        ]);
                        } catch (Exception $e) {
                            CASHSystem::errorLog($e->getMessage());
                        }

					} else {

                		try {
							$people_analytics_basic->total = $new_total;
							$people_analytics_basic->save();
                        } catch (Exception $e) {
                            CASHSystem::errorLog($e->getMessage());
                        }
					}
                }
		}

		return $result;
	}

	/**
	 * Adds a new user to the system, setting login details
	 *
	 * @param {string} $address -  the email address in question
	 * @param {string} $password - the password
	 * @return array|false
	 */
	protected function addLogin($address,$password,$is_admin=0,$username='',$display_name='Anonymous',$first_name='',$last_name='',$organization='',$address_country='',$force52compatibility=false,$data='',$address_postalcode='') {

		$id_request = new CASHRequest(
			array(
				'cash_request_type' => 'people',
				'cash_action' => 'getuseridforaddress',
				'with_security_credentials' => true,
				'address' => $address
			)
		);
		if ($id_request->response['payload']) {
			// if we're adding an admin login and the user isn't currently an admin, edit:
			if ($is_admin && !$id_request->response['payload']['is_admin']) {
				// add admin status:

				$user = $this->orm->find(People::class, $id_request->response['payload']['id']);
				$user->is_admin = $is_admin;
				$user->save();

				// return false on error
				if (!$user) {
					return false;
				}
				if (!trim($id_request->response['payload']['api_key'])) {
					// if the API key is empty then set credentials:
					$this->setAPICredentials($id_request->response['payload']['id']);
				}
				return $id_request->response['payload']['id'];
			} else {
				// return the id as success
				return $id_request->response['payload']['id'];
			}
		}

		if ($password !== '') {
			$password_hash = $this->generatePasswordHash($password,$force52compatibility);
		} else {
			// blank string for password hash if not an admin — will disallow logins withou
			// a reset, but that's a good thing. and for sign-in style elements we'll simly
			// provide a password reset (a la the admin), which is good UX anyway. this will
			// greatly speed things up...
			$password_hash = '';
		}

		$user = $this->orm->create(People::class, [
            'email_address' => $address,
            'password' => $password_hash,
            'username' => strtolower($username),
            'display_name' => $display_name,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'organization' => $organization,
            'address_country' => $address_country,
            'is_admin' => $is_admin,
            'address_postalcode' => $address_postalcode,
            'data' => $data
        ]);

		if ($user && $is_admin) {
			$this->setAPICredentials($user->id);
		}
		return $user->id;
	}

	/**
	 * Removes user to the system
	 *
	 * @param {string} $address -  the email address in question
	 * @return bool
	 */
	protected function deleteLogin($address) {
		// doing this via address not only follows conventions established in handling people,
		// but guarantees we're getting the right user id. no passing in the wrong id and watching
		// the script choke...
		$user = $this->orm->findWhere(People::class, ['email_address'=>$address] );

		if ($user) {
			// mass delete all the mass deletable stuff
			$tables = ['assets','calendar_events','commerce_items','elements','elements_campaigns','people_contacts','people_mailings','system_connections','system_lock_codes','system_metadata','system_settings','system_templates'];

			foreach ($tables as $table) {
                $this->db->table($table)->where('user_id', $user->id)->delete();
			}

			// get all lists via PeoplePlant and delete them properly. this means we'll
			// also remove any list members and webhooks associated with them
			$lists_request = new CASHRequest(
				array(
					'cash_request_type' => 'people',
					'cash_action' => 'getlistsforuser',
					'user_id' => $user->id
				)
			);
			if ($lists_request->response['payload']) {
				foreach ($lists_request->response['payload'] as $list) {
					$list_delete_request = new CASHRequest(
						array(
							'cash_request_type' => 'people',
							'cash_action' => 'deletelist',
							'list_id' => $list->id
						)
					);
				}
			}

			// wipe yourself off, man. you dead. http://www.youtube.com/watch?v=XpF2EH3_T1w
			$result = $user->delete();

			return $result;

			// ;(
		}
	}

	/**
	 * Resets email/password credentials for a user
	 *
	 * @param {int} $user_id -  the user
	 * @return array|false
	 */protected function setLoginCredentials($user_id,$address=false,$password=false,$username=false,$is_admin=false,$display_name=false,$url=false) {

		if ($password) {
			$password_hash = $this->generatePasswordHash($password);
		}

		$credentials = array();
		if ($address) {
			$email_request = new CASHRequest(
				array(
					'cash_request_type' => 'people',
					'cash_action' => 'getuseridforaddress',
					'address' => $address
				)
			);
			if (!$email_request->response['payload']) {
				// only go if not found. if it's found and different, then we can't have that email.
				// if it's found but the same, then why bother changing?
				$credentials['email_address'] = $address;
			}
		}
		if ($password) {
			$credentials['password'] = $password_hash;
		}
		if ($is_admin) {
			$credentials['is_admin'] = $is_admin;
			$this->setAPICredentials($user_id);
		}
		if ($display_name) {
			$credentials['display_name'] = $display_name;
		}
		if ($url) {
			$credentials['url'] = $url;
		}
		if ($username) {
			$id_request = new CASHRequest(
				array(
					'cash_request_type' => 'people',
					'cash_action' => 'getuseridforusername',
					'username' => $username
				)
			);
			if (!$id_request->response['payload']) {
				// only go if not found. same reasons as above. seriously. you know what i'm saying.
				$credentials['username'] = $username;
			} else {
				// check for the username — if it doesn't exist we're good. if it DOES exist then we
				// have a little work. check for admin status, erase the old name if not an admin then
				// mark the change as okay and move on.

				$user = $this->orm->find(People::class, $id_request->response['payload']);

				if ($user) {
					// we've found someone with this username already
					if (!$user->is_admin) {
						// okay so the jerk with this username isn't an admin (the account is deleted)
						// so let's try to unset the username
						$user->username = "";
						$user->save();

                        $credentials['username'] = $username;
					}
				}
			}
		}

		if (count($credentials)) {

            // reset the data field for subscriptions
            $credentials['data'] = "{}";

            if ($user = $this->orm->find(People::class, $user_id)) {
                $user->update($credentials);
                return $user->id;
            }
		}

		return false;

	}

	/**
	 * Sets or resets the password reset for a user
	 *
	 * @return key(md5 hash)|false
	 */protected function setResetFlag($address) {

		$user = $this->orm->findWhere(People::class, ['email_address'=>$address] );

		if ($user) {
			$user_id = $user->id;
			// first remove any password resets for the same user
			$this->db->table("people_resetpassword")->where('id', $user_id)->delete();

			$key = md5($user_id . rand(976654,1234567267));

			$reset = $this->orm->create(PeopleResetPassword::class, [
                'user_id'=>$user_id,
                'key'=>$key
            ]);

			if ($reset) {
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
	 */
	protected function validateResetFlag($address,$key) {

	 	$user = $this->orm->findWhere(People::class, ['email_address'=>$address] );

		if ($user) {
			$reset = $this->orm->findWhere(PeopleResetPassword::class, ['user_id'=>$user->id, 'key'=>$key] );

			// in case we get multiple results back, just get the latest reset request.
			if (is_array($reset)) {
				$reset = array_pop($reset);
			}

			if ($reset) {
				if (($reset->creation_date + 86400) > time()) {
					return true;
				}
			}
		}

        return false;
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

		$user = $this->orm->find(People::class, $user_id);
		$user->update($credentials);

		if ($user) {
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
	 */
	protected function getAPICredentials($user_id) {

		$user = $this->orm->find(People::class, $user_id);

		if ($user) {
			return array(
				'api_key' => $user->api_key,
				'api_secret' => $user->api_secret
			);
		}

		return false;
	}

	/**
	 * Verifies API credentials and returns authorization type (api_key || api_fullauth || none) and user_id
	 *
	 * @param {int} $user_id -  the user
	 * @return array|false
	 */
	protected function validateAPICredentials($api_key,$api_secret=false) {
		$auth_type = 'none';
		if (!$api_secret) {
			$auth_type = 'api_key';
			$user = $this->orm->findWhere(People::class, ['api_key'=>$api_key] );
		} else {

			$auth_type = 'api_fullauth';
            $user = $this->orm->findWhere(People::class, ['api_key'=>$api_key, 'api_secret'=>$api_secret] );
		}

		if ($user) {
			return array(
				'auth_type' => $auth_type,
				'user_id' => $user->id
			);
		}

		return false;
	}

	/**
	 * Removes system settings of the given type for a user — be careful with wild cards. (Don't
	 * use them unless you want to delete all system settings for a user. So, you know, don't.)
	 *
	 * @return bool
	 */
	protected function deleteSettings($user_id,$type) {

		if ($this->orm->delete(SystemSettings::class, ['type'=>$type, 'user_id'=>$user_id])) {
			return true;
		}

		return false;
	}

	/**
	 * Gets settings of the given type for a user. Set return_json to true and the system will
	 * return the stored JSON without decoding.
	 *
	 * @return string|array|false
	 */
	protected function getSettings($user_id,$type,$return_json=false) {

		$setting = $this->orm->findWhere(SystemSettings::class, ['type'=>$type,'user_id'=>$user_id] );

		if ($setting) {
			if ($return_json) {
				return json_encode($setting->toArray());
			} else {
				return $setting->value;
			}
		} else {
			$this->contextual_message = "No settings of type `$type` found for this user.";
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

        $setting = $this->orm->findWhere(SystemSettings::class, ['type'=>$type,'user_id'=>$user_id] );

		if ($setting) {
			$setting->update(['value'=>$value]);
			return true;
		} else {
			// if this doesn't exist we need to create a new setting entry
			$setting = $this->orm->create(SystemSettings::class, [
                'type'=>$type,
				'user_id'=>$user_id,
				'value'=>$value
			]);

			if ($setting) return true;
		}

		return $this->error('404')->message("Error creating or updating `$type` setting for this user.");
	}

	/**
	 * Removes a user page/embed template
	 *
	 * @return bool
	 */
	protected function deleteTemplate($template_id,$user_id=false) {

		if ($user_id) {
			$template = $this->orm->delete(SystemTemplate::class, ['user_id'=>$user_id,'id'=>$template_id] );
		} else {
			$template = $this->orm->delete(SystemTemplate::class, ['id'=>$template_id]);
		}

		if ($template) {
			return true;
		}

		return false;
	}

	/**
	 * Gets a user page/embed template for display.
	 *
	 * @return string|false
	 */
	protected function getTemplate($template_id,$user_id=false,$all_details=false) {

        $conditions = array(
            "id" => $template_id
        );

        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        $template = $this->orm->findWhere(SystemTemplate::class, $conditions);

		if ($template) {
			if (!$all_details) {
				return $template->template;
			} else {
				return $template->toArray();
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets all user page/embed template for display.
	 *
	 * @return string|false
	 */
	protected function getTemplatesForUser($user_id,$type=false) {

		$conditions = array(
			"user_id" => $user_id
		);

		if ($type) {
			$conditions['type'] = $type;
		}

		$templates = $this->orm->findWhere(SystemTemplate::class, $conditions);

		return $templates;
	}

	/**
	 * Gets the latest page/embed template for a given user.
	 *
	 * @return string|false
	 */
	protected function getNewestTemplate($user_id,$type='page',$all_details=false) {

		$template = $this->db->table('system_templates')
			->where('user_id', $user_id)
			->where('type', $type)
			->orderBy("creation_date", "DESC")
			->limit(1)->get();

		if ($template) {
			if (!$all_details) {
				return $template->template;
			} else {
				return $template;
			}
		} else {
			return false;
		}
	}

	/**
	 * Adds/edits a user page/embed template
	 *
	 * @return bool
	 */
	protected function setTemplate($user_id,$type=false,$name=false,$template=false,$template_id=false) {
		$final_edits = array_filter(
			array(
				'user_id' => $user_id,
				'type' => $type,
				'name' => $name,
				'template' => $template
			),
			function($value) {
               return CASHSystem::notExplicitFalse($value);
			}
		);

		if ($template_id) {
			$template = $this->orm->findWhere(SystemTemplate::class, ['id'=>$template_id,'user_id'=>$user_id] );
			$template->update($final_edits);
		} else {
			// if no template id we're doing an add, so make sure the type has been set
			// correctly by checking for false and adding a default
			if (!$type) {
				$final_edits['type'] = 'page';
			}
            $template = $this->orm->create(SystemTemplate::class, $final_edits);
		}

		if ($template) {
            return $template->id;
		}

		return false;
	}

	/**
	 * Retrieves the last known UID or if none are found creates and returns a
	 * random UID as a starting point
	 *
	 * @return string
	 */
	protected function getLastLockCode() {

	 	$lock_codes = $this->db->table('system_lock_codes')
			->select(['uid'])->orderBy('id', 'DESC')
			->limit(1)->get();

		if ($lock_codes) {

			if (is_array($lock_codes)) {
                $code = $lock_codes[0]->uid;
			} else {
                $code = $lock_codes->uid;
			}

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

		$lock_code = $this->orm->create(SystemLockCode::class, [
            'uid' => $code,
            'scope_table_alias' => $scope_table_alias,
            'scope_table_id' => $scope_table_id,
            'user_id' => $user_id
        ]);

		if ($lock_code) {
			return $code;
		} else {
			return false;
		}
	}

    protected function addBulkLockCodes($scope_table_alias,$scope_table_id,$user_id, $count){

	 	$codes = [];
	 	for($i=0; $i<$count;$i++) {
	 		$codes[] = $this->generateCode(
                $this->lock_code_chars['all_chars'],
                $this->lock_code_chars['code_break'],
                $this->getLastLockCode()
            );
		}

        $code_insert = [];

	 	// we can just create o
		if (count($codes) < 50001) {

            foreach ($codes as $code) {
                $code_insert[] = [
                	'uid'=>$code,
					'scope_table_alias'=>$scope_table_alias,
					'scope_table_id'=>$scope_table_id,
					'user_id'=>$user_id
				];
            }

			// bulk create codes
            $create_codes = $this->db->table('system_lock_codes')->insertIgnore($code_insert);

		} else {
			// we actually don't need this for the foreseeable future. can't think of a single instance where we'd be doing more than 50k codes at once
		}

        if ($create_codes) {
            return true;
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
		$lock_code = $this->getLockCode($code);
		if ($lock_code) {
			// check against optional arguments — if they're found then make sure they match
			// the data stored with the code...if not invalidate the request and return false
			$proceed = true;
			if ($scope_table_alias && ($scope_table_alias != $lock_code->scope_table_alias)) {
				$proceed = false;
			}
			if ($scope_table_id && ($scope_table_id != $lock_code->scope_table_id)) {
				$proceed = false;
			}
			if ($user_id && ($user_id != $lock_code->user_id)) {
				$proceed = false;
			}
			if ($proceed) {
				// details found
				if (!$lock_code->claim_date) {

					$lock_code->claim_date = time();
					$lock_code->save();

					if ($lock_code) {
						return [
							'scope_table_id' => $lock_code->scope_table_id,
							'scope_table_alias' => $lock_code->scope_table_alias
						];
					} else {
						return false;
					}
				} else {
					// allow retries for four hours after claim
					if (($lock_code->claim_date + 14400) > time()) {
                        return [
                            'scope_table_id' => $lock_code->scope_table_id,
                            'scope_table_alias' => $lock_code->scope_table_alias
                        ];
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

		$lock_code = $this->orm->findWhere(SystemLockCode::class, ['uid'=>$code] );

		if ($lock_code) {
			if (is_array($lock_code)) {
				return $lock_code[0];
			} else {
				return $lock_code;
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets all lock codes for a given resource.
	 *
	 * @return array|false
	 */
	protected function getLockCodes($scope_table_alias,$scope_table_id,$user_id=false) {
        $conditions = array(
            "scope_table_alias" => $scope_table_alias,
            "scope_table_id" => $scope_table_id
        );

        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        $lock_codes = $this->orm->findWhere(SystemLockCode::class, $conditions, true);

        if ($lock_codes) {
            return $lock_codes;
        }

        return false;
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

	/*
	 *
	 * SESSION TEST/SET FOR __EXTERNAL__ sessions
	 *
	 */
	protected function startJSSession() {
		$r = new CASHRequest();
		$session_details = $r->startSession(false,true); // second false sandboxes

		if ($session_details['newsession']) {
			if (!$session_details['expiration']) {
				$session_details['expiration'] = time() + $this->cash_session_timeout;
			}
			return json_encode(array('expiration' => $session_details['expiration'], 'id' => $session_details['id']));
		} else {
			return json_encode($session_details);
		}
	}

} // END class
?>
