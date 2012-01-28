<?php
/**
 * The AdminCore class handles basic request/reponse issues, as well as providing
 * universal storage for data/responses across the lifetime of a page.
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */class AdminCore  {
	protected $stored_responses;
	protected $stored_data;
	protected $effective_user_id;
	
	// default admin settings:
	protected $default_user_settings = array(
		'banners' => array(
			'mainpage' => true,
			'elements' => true,
			'assets' => true,
			'people' => true,
			'commerce' => true,
			'calendar' => true
		)
	);
	
	public function __construct($effective_user_id=false) {
		$this->stored_responses = array();
		$this->stored_data = array();
		if ($effective_user_id) {
			$this->effective_user_id = $effective_user_id;
		}
	}

	public function getUserSettings() {
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'getsettings',
				'type' => 'cashmusic_admin_settings',
				'user_id' => $this->effective_user_id
			)
		);
		if ($settings_request->response['payload']['value']) {
			return json_decode($settings_request->response['payload']['value'],true);
		} else {
			$this->setUserSettings($this->default_user_settings);
			return $this->default_user_settings;
		}
	}

	public function setUserSettings($settings_array) {
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'setsettings',
				'type' => 'cashmusic_admin_settings',
				'value' => json_encode($settings_array),
				'user_id' => $this->effective_user_id
			)
		);
		return $settings_request;
	}

	/**
	 * Does a CASH Request and stores the response in $stored_responses
	 *
	 * @return array
	 */public function requestAndStore($request_array,$store_name) {
		$cash_admin_request = new CASHRequest($request_array);
		$this->stored_responses[$store_name] = $cash_admin_request->response;
		unset($cash_admin_request);
		return $this->stored_responses[$store_name];
	}

	/**
	 * Gets a previously stored CASH Response
	 *
	 * @return array
	 */public function getStoredResponse($store_name,$return_payload=false) {
		if (isset($this->stored_responses[$store_name])) {
			if ($return_payload) {
				return $this->stored_responses[$store_name]['payload'];
			} else {
				return $this->stored_responses[$store_name];
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets previously stored data
	 *
	 * @return array
	 */public function storeData($data,$store_name) {
			$this->stored_data[$store_name] = $data;
	}

	/**
	 * Gets previously stored data
	 *
	 * @return array
	 */public function getStoredData($store_name) {
		if (isset($this->stored_data[$store_name])) {
			return $this->stored_data[$store_name];
		} else {
			return false;
		}
	}

} // END class 
?>
