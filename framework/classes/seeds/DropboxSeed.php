<?php
/**
 * Simple class for working with Dropbox
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 **/

require_once(CASH_PLATFORM_ROOT.'/lib/dropbox/autoload.php');

use \Dropbox as dbx;

class DropboxSeed extends SeedBase {

	private $client,
			$access_token, 
			$app_key,
			$app_secret,
			$error_message=false;

	public function __construct($user_id, $connection_id) {

		$this->settings_type = 'com.dropbox';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		$this->connectDB();

		if ($this->getCASHConnection()) {

			$connections = CASHSystem::getSystemSettings('system_connections');

			if (isset($connections['com.dropbox'])) {
				$app_key      = $connections['com.dropbox']['app_key'];
				$app_secret   = $connections['com.dropbox']['app_secret'];
			}

		} else {
			$this->error_message = 'could not get connection';
		}
	}

	public static function getWebAuthClient($redirect_uri) {

		$connections = CASHSystem::getSystemSettings('system_connections');
		
		if (isset($connections['com.dropbox'])) {

			$app_info = new dbx\AppInfo(
				$connections['com.dropbox']['app_key'],
				$connections['com.dropbox']['app_secret']
			);

			$client_identifier = "CASH Music/1.0";
			$csrf_token_store = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');

			return new dbx\WebAuth($app_info, $client_identifier, $redirect_uri, $csrf_token_store);

		} else {
			return false;
		}		
	}

	public static function getAuthorizationUrl($redirect_uri) {

		$auth_client = DropboxSeed::getWebAuthClient($redirect_uri);
		if (!$auth_client) {
			return false;
		}

		return $auth_client->start();
	}

	public static function getRedirectMarkup($data=false) {

		$connections = CASHSystem::getSystemSettings('system_connections');
		
		if (isset($connections['com.dropbox'])) {

			$login_url = DropboxSeed::getAuthorizationUrl(
				$connections['com.dropbox']['redirect_uri']
			);
		}

		if ($login_url) {

			$return_markup  = '<h4>Dropbox</h4>';
			$return_markup .= '<p>This will redirect you to a secure login at Dropbox and bring you right back.</p>';
			$return_markup .= '<a href="' . $login_url . '" class="button">Connect your Dropbox</a>';

			return $return_markup;

		} else {
			return 'Please add default Dropbox credentials.';
		}
	}

	public static function handleRedirectReturn($data=false) {
	}

} // END class 
?>
