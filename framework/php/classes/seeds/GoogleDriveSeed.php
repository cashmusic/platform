<?php
/**
 * Google Drive connection seed
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
class GoogleDriveSeed extends SeedBase {
	private $client, $access_token, $client_id, $client_secret, $redirect_uri, $error_message=false;
	private $scopes = array(
		'https://www.googleapis.com/auth/drive.file',
		'https://www.googleapis.com/auth/drive.readonly',
		'https://www.googleapis.com/auth/userinfo.email',
		'https://www.googleapis.com/auth/userinfo.profile'
	);

	public function __construct($user_id, $connection_id) {
		$this->settings_type = 'com.google.drive';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		if ($this->getCASHConnection()) {
			require_once(CASH_PLATFORM_ROOT.'/lib/google/Google_Client.php');
			
			// check the access_expires time against right now. if the access_token is 
			// expired call $client->refreshToken(refresh_token) and get a new access_token
			// and reset the connection with new access_token, access_expires, and refresh_token

		} else {
			$this->error_message = 'could not get connection';
		}
	}

	public static function getRedirectMarkup($data=false) {
		$connections = CASHSystem::getSystemSettings('system_connections');
		
		if (isset($connections['com.google.drive'])) {
			$login_url = GoogleDriveSeed::getAuthorizationUrl(
				$connections['com.google.drive']['client_id'],
				$connections['com.google.drive']['redirect_uri'],
				array(
					'https://www.googleapis.com/auth/drive.file',
					'https://www.googleapis.com/auth/drive.readonly',
					'https://www.googleapis.com/auth/userinfo.email',
					'https://www.googleapis.com/auth/userinfo.profile'
				));
			$return_markup = '<h3>Connect to Google Drive</h3>'
						   . '<p>This will redirect you to a secure login at Google and bring you right back.</p>'
						   . '<a href="' . $login_url . '" class="mockbutton">Connect your Google Drive</a>';
			return $return_markup;
		} else {
			return 'Please add default google drive app credentials.';
		}
	}

	public static function handleRedirectReturn($data=false) {
		if (isset($data['code'])) {
			$connections = CASHSystem::getSystemSettings('system_connections');
			if (isset($connections['com.google.drive'])) {
				$credentials = GoogleDriveSeed::exchangeCode(
					$data['code'],
					$connections['com.google.drive']['client_id'],
					$connections['com.google.drive']['client_secret'],
					$connections['com.google.drive']['redirect_uri']
				);
				$user_info = GoogleDriveSeed::getUserInfo(
					$credentials,
					$connections['com.google.drive']['client_id'],
					$connections['com.google.drive']['client_secret']
				);
				if ($user_info) {
					$email_address = $user_info['email'];
					$user_id       = $user_info['id'];
				} else {
					$email_address = false;
					$user_id       = false;
				}
				$credentials_array = json_decode($credentials, true);
				if (isset($credentials_array['refresh_token'])) {
					// we can safely assume (AdminHelper::getPersistentData('cash_effective_user') as the OAuth 
					// calls would only happen in the admin. If this changes we can fuck around with it later.
					$new_connection = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
					$result = $new_connection->setSettings(
						$email_address . ' (Google Drive)',
						'com.google.drive',
						array(
							'user_id'        => $user_id,
							'email_address'  => $email_address,
							'access_token'   => $credentials,
							'access_expires' => $credentials_array['created'] + $credentials_array['created'] - 180,
							'refresh_token'  => $credentials_array['refresh_token']
						)
					);
					if ($result) {
						AdminHelper::formSuccess('Success. Connection added. You\'ll see it below.','/settings/connections/');
					} else {
						AdminHelper::formFailure('Error. Something just didn\'t work right.','/settings/connections/');
					}
				} else {
					return 'Could not find a refresh token from google';
				}
			} else {
				return 'Please add default google drive app credentials.';
			}
		} else {
			return 'There was an error. (session) Please try again.';
		}
	}

	/**
	 * Retrieve the authorization URL.
	 *
	 * @param String $email_address User's e-mail address.
	 * @return String Authorization URL to redirect the user to.
	 */
	public static function getAuthorizationUrl($client_id,$redirect_uri,$scopes,$email_address=false) {
		require_once(CASH_PLATFORM_ROOT.'/lib/google/Google_Client.php');
		$client = new Google_Client();

		$client->setClientId($client_id);
		$client->setRedirectUri($redirect_uri);
		$client->setAccessType('offline');
		$client->setApprovalPrompt('force');
		$client->setState('preauth');
		$client->setScopes($scopes);
		$auth_url = $client->createAuthUrl();
		if ($email_address) {
			$client->setState('refresh');
			$tmpUrl = parse_url($auth_url);
			$query = explode('&', $tmpUrl['query']);
			$query[] = 'user_id=' . urlencode($email_address);
			return $tmpUrl['scheme'] . '://' . $tmpUrl['host'] . $tmpUrl['port'] .
				$tmpUrl['path'] . '?' . implode('&', $query);
		} else {
			return $auth_url;
		}
	}

	/**
	 * Exchange an authorization code for OAuth 2.0 credentials.
	 *
	 * @param String $authorization_code Authorization code to exchange for OAuth 2.0 credentials.
	 * @return String Json representation of the OAuth 2.0 credentials.
	 */
	public static function exchangeCode($authorization_code,$client_id,$client_secret,$redirect_uri) {
		require_once(CASH_PLATFORM_ROOT.'/lib/google/Google_Client.php');
		try {
			$client = new Google_Client();
			$client->setClientId($client_id);
			$client->setClientSecret($client_secret);
			$client->setRedirectUri($redirect_uri);
			return $client->authenticate($authorization_code);
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Send a request to the UserInfo API to retrieve the user's information.
	 *
	 * @param String credentials OAuth 2.0 credentials to authorize the request.
	 * @return Userinfo User's information.
	 */
	public static function getUserInfo($credentials,$client_id,$client_secret) {
		require_once(CASH_PLATFORM_ROOT.'/lib/google/Google_Client.php');
		require_once(CASH_PLATFORM_ROOT.'/lib/google/contrib/Google_Oauth2Service.php');
		$client = new Google_Client();
		$client->setUseObjects(true);
		$client->setClientId($client_id);
  		$client->setClientSecret($client_secret);
		$client->setAccessToken($credentials);
		$service = new Google_Oauth2Service($client);
		$user_info = null;
		try {
			$user_info = $service->userinfo->get();
		} catch (Google_Exception $e) {
			// $this->error_message = 'An error occurred: ' . $e->getMessage();
			return false;
		}
		if (is_array($user_info)) {
			return $user_info;
		} else {
			return false;
		}
	}
} // END class
?>
