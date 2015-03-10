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

			$cash_page_request = new CASHRequest(null);
			$csrf_token = $cash_page_request->sessionGet('dropbox_csrf_token');

			if (!$csrf_token) {

				$user_id = AdminHelper::getPersistentData('cash_effective_user');
				$csrf_token = sha1('com.dropbox:user-{$user_id}');
			}

			$client_identifier = "CASH Music/1.0";
			$csrf_array = array(
				'dropbox-auth-csrf-token' => $csrf_token,
			);

			$csrf_token_store = new dbx\ArrayEntryStore($csrf_array, 'dropbox-auth-csrf-token');

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

		$url = $auth_client->start();
		$csrf_token = $auth_client->getCsrfTokenStore()->get();

		$cash_page_request = new CASHRequest(null);
		$cash_page_request->sessionSet('dropbox_csrf_token', $csrf_token);

		return $url;
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

		if (!isset($data['state'])) {
			return "Please start the Dropbox authentication flow from the beginning.";
		}

		$connections = CASHSystem::getSystemSettings('system_connections');
		
		if (!isset($connections['com.dropbox'])) {
			return 'Please add default Dropbox credentials.';
		}

		$auth_client = DropboxSeed::getWebAuthClient($connections['com.dropbox']['redirect_uri']);

		try {
			list($token, $user_id) = $auth_client->finish($data);
		} catch (Exception $e) {
			$token = false;
		}

		if (!$token) {
			return "The Dropbox authentication flow failed - please try again.";
		}

		$new_connection = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));

		$result = $new_connection->setSettings(
			'Dropbox (' . $user_id . ')',
			'com.dropbox',
			array(
				'access_token'	=> $token,
				'user_id' 			=> $user_id,
			)
		);

		if (isset($data['return_result_directly'])) {
			return $result;

		} else {

			if ($result) {
				AdminHelper::formSuccess('Success. Connection added. You\'ll see it in your list of connections.','/settings/connections/');
			} else {
				AdminHelper::formFailure('Error. Something just didn\'t work right.','/settings/connections/');
			}
		}
	}
}
?>
