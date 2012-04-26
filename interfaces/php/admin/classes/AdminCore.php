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
	public $page_data;
	
	// default admin settings:
	protected $default_user_settings = array(
		'banners' => array(
			'mainpage' => true,
			'elements' => true,
			'assets' => true,
			'people' => true,
			'commerce' => true,
			'calendar' => true
		),
		'favorite_assets' => array()
	);
	
	public function __construct($effective_user_id=false) {
		$this->stored_responses = array();
		$this->stored_data = array();
		$this->page_data = array();
		if ($effective_user_id) {
			$this->effective_user_id = $effective_user_id;
		}
	}
	
	/**
	 * Performs basic tasks each time a user logs in
	 *
	 */public function runAtLogin() {
		// sync all assets
		$c = new CASHConnection($this->effective_user_id);
		$applicable_connections = $c->getConnectionsByScope('assets');
		if (is_array($applicable_connections)) {
			foreach ($applicable_connections as $connection) {
				$sync_request = new CASHRequest(
					array(
						'cash_request_type' => 'asset', 
						'cash_action' => 'syncconnectionassets',
						'connection_id' => $connection['id']
					)
				);
			}
		}
	}


	/**********************************************
	 *
	 * USER SETTINGS
	 *
	 *********************************************/
	/**
	 * Gets the 'cashmusic_admin_settings' for the current user
	 *
	 * @return array
	 */public function getUserSettings() {
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'getsettings',
				'type' => 'cashmusic_admin_settings',
				'user_id' => $this->effective_user_id
			)
		);
		if ($settings_request->response['payload']) {
			return $settings_request->response['payload'];
		} else {
			$this->setUserSettings($this->default_user_settings);
			return $this->default_user_settings;
		}
	}

	/**
	 * Sets user settings in the database, keyed as 'cashmusic_admin_settings'
	 *
	 * @return object / bool
	 */public function setUserSettings($settings_array) {
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'setsettings',
				'type' => 'cashmusic_admin_settings',
				'value' => $settings_array,
				'user_id' => $this->effective_user_id
			)
		);
		return $settings_request;
	}

	/**********************************************
	 *
	 * ASSETS
	 *
	 *********************************************/
	/**
	 * Marks an asset as a favorite
	 *
	 */public function favoriteAsset($asset_id) {
		$user_settings = $this->getUserSettings();
		if (!in_array($asset_id,$user_settings['favorite_assets'])) {
			$user_settings['favorite_assets'][] = $asset_id;
		}
		$this->setUserSettings($user_settings);
	}

	/**
	 * Removes favorite status from an asset (or ignores if not a favorite)
	 *
	 */public function unFavoriteAsset($asset_id) {
		$user_settings = $this->getUserSettings();
		$key = array_search($asset_id,$user_settings['favorite_assets']);
		if ($key !== false) {
			unset($user_settings['favorite_assets'][$key]);
		}
		$this->setUserSettings($user_settings);
	}

	/**
	 * Returns all assets marked as favorites by the user
	 *
	 * @return array / bool
	 */public function getAllFavoriteAssets() {
		$user_settings = $this->getUserSettings();
		if (!count($user_settings['favorite_assets'])) {
			return false;
		} else {
			return $user_settings['favorite_assets'];
		}
	}
	
	/**
	 *  Detects if a given asset_id has been marked as a favorite
	 *
	 * @return bool
	 */public function isAssetAFavorite($asset_id) {
		$favorites = $this->getAllFavoriteAssets();
		return in_array($asset_id,$favorites);
	}

	/**********************************************
	 *
	 * DATA & REQUEST STORAGE
	 *
	 *********************************************/
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
