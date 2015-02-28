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

use \Dropbox as dbx;

class DropboxSeed extends SeedBase {

	private $client,
			$auth_client,
			$access_token, 
			$redirect_uri, 
			$app_key,
			$app_secret,
			$error_message=false;

	public function __construct($user_id, $connection_id) {

		$this->settings_type = 'com.dropbox';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		$this->connectDB();

		if ($this->getCASHConnection()) {

			require_once(CASH_PLATFORM_ROOT.'/lib/dropbox/autoload.php"');
			
			$connections = CASHSystem::getSystemSettings('system_connections');

			if (isset($connections['com.dropbox'])) {

				$app_key      = $connections['com.dropbox']['app_key'];
				$app_secret   = $connections['com.dropbox']['app_secret'];
				$redirect_uri = $connections['com.dropbox']['redirect_uri'];
			}

			$app_info = array(
				'key' 		=> $app_key,
				'secret' 	=> $app_secret,
			);

 			$client_identifier = "CASH Music/1.0";
 			$csrf_token_store = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');

 			$this->auth_client = dbx\WebAuth($app_info, $client_identifier, $redirect_uri, $csrf_token_store);

		} else {
			$this->error_message = 'could not get connection';
		}
	}

	public static function getAuthorizationUrl($client_id, $redirect_uri) {
		$this->auth_client->start();
	}

	public static function getRedirectMarkup($data=false) {

		$connections = CASHSystem::getSystemSettings('system_connections');
		
		if (isset($connections['com.dropbox'])) {

			$login_url = DropboxSeed::getAuthorizationUrl(
				$connections['com.google.drive']['client_id'],
				$connections['com.google.drive']['redirect_url']
			);

			$return_markup  = '<h4>Dropbox</h4>\n';
			$return_markup .= '<p>This will redirect you to a secure login at Dropbox and bring you right back.</p>\n';
			$return_markup .= '<a href="' . $login_url . '" class="button">Connect your Dropbox</a>\n';

			return $return_markup;

		} else {
			return 'Please add default Dropbox credentials.';
		}
	}

	public static function handleRedirectReturn($data=false) {
	}

} // END class 
?>
