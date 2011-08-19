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
			switch ($this->action) {
				case 'validatelogin':
					if (!$this->checkRequestMethodFor('direct')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('address','password')) { return $this->sessionGetLastResponse(); }
					$result = $this->validateLogin($this->request['address'],$this->request['password']);
					return $this->response->pushResponse(
						200,$this->request_type,$this->action,
						$result,
						'success. id or false included in payload'
					);
					break;
				case 'validateadminlogin':
					if (!$this->checkRequestMethodFor('direct')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('address','password')) { return $this->sessionGetLastResponse(); }
					$result = $this->validateLogin($this->request['address'],$this->request['password'],true);
					return $this->response->pushResponse(
						200,$this->request_type,$this->action,
						$result,
						'success. id or false included in payload'
					);
					break;
				case 'setlogin':
					if (!$this->checkRequestMethodFor('direct')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('address','password')) { return $this->sessionGetLastResponse(); }
					$result = $this->setLogin($this->request['address'],$this->request['password']);
					if ($result) {
						return $this->pushSuccess($result,'success. true or false included in payload');
					} else {
						return $this->pushFailure('there was an error');
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
	 * Generates a password hash and compares against the stored hash
	 *
	 * @param {string} $address -  the email address in question
	 * @param {string} $password - the password
	 * @return array|false
	 */public function validateLogin($address,$password,$require_admin=false) {
		$password_hash = hash_hmac('sha256', $password, $this->salt);
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
		if ($password_hash == $result[0]['password']) {
			if (($require_admin && $result[0]['is_admin']) || !$require_admin) {
				return $result[0]['id'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Adds a new user to the system, setting login details
	 *
	 * @param {string} $address -  the email address in question
	 * @param {string} $password - the password
	 * @return array|false
	 */public function setLogin($address,$password) {
		$password_hash = hash_hmac('sha256', $password, $this->salt);
		$result = $this->db->setData(
			'users',
			array(
				'email_address' => $address,
				'password' => $password_hash
			)
		);
		return $result;
	}
		
} // END class 
?>