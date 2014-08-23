<?php
/**
 * Google Drive connection seed
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
 * This file is generously sponsored by CaseyContrarian 
 * CaseyContrarian Hearts CASH Music
 *
 **/
class GoogleDriveSeed extends SeedBase {
	private $client, 
			$drive_service, 
			$access_token, 
			$client_id, 
			$client_secret, 
			$redirect_uri, 
			$app_id,
			$api_key,
			$user_email,
			$error_message=false,
			$scopes = array(
				'https://www.googleapis.com/auth/drive',
				'https://www.googleapis.com/auth/userinfo.email',
				'https://www.googleapis.com/auth/userinfo.profile'
			);

	public function __construct($user_id, $connection_id) {
		$this->settings_type = 'com.google.drive';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		if ($this->getCASHConnection()) {
			$connections = CASHSystem::getSystemSettings('system_connections');

			if (isset($connections['com.google.drive'])) {
				$this->client_id     = $connections['com.google.drive']['client_id'];
				$this->client_secret = $connections['com.google.drive']['client_secret'];
				$this->redirect_uri  = $connections['com.google.drive']['redirect_uri'];
				$this->app_id        = $connections['com.google.drive']['app_id'];
				$this->api_key       = $connections['com.google.drive']['api_key'];
				$this->access_token  = $this->settings->getSetting('access_token');
				$this->email_address = $this->settings->getSetting('email_address');

				require_once(CASH_PLATFORM_ROOT.'/lib/google/Google_Client.php');
				require_once(CASH_PLATFORM_ROOT.'/lib/google/contrib/Google_DriveService.php');
				$this->client = new Google_Client();
				$this->client->setUseObjects(false);
				$this->client->setClientId($this->client_id);
				$this->client->setClientSecret($this->client_secret);
				$this->client->setRedirectUri($this->redirect_uri);
				$this->client->setScopes($this->scopes);
				$this->client->setAccessType('offline');

				// check the access_expires time against right now. if the access_token is 
				// expired call $client->refreshToken(refresh_token) and get a new access_token
				// and reset the connection with new access_token, access_expires, and refresh_token
				if (time() > $this->settings->getSetting('access_expires')) {
					$refresh_token = $this->settings->getSetting('refresh_token');
					$this->client->refreshToken($refresh_token);
					$credentials = $this->client->getAccessToken();
					if ($credentials) {
						$this->access_token = $credentials;
						$credentials_array = json_decode($credentials, true);
						$this->settings->updateSettings(
							array(
								'user_id'        => $this->user_id,
								'email_address'  => $this->settings->getSetting('email_address'),
								'access_token'   => $credentials,
								'access_expires' => $credentials_array['created'] + $credentials_array['expires_in'],
								'refresh_token'  => $refresh_token
							)
						);
					}
				}

				$this->client->setAccessToken($this->access_token);
				$this->drive_service = new Google_DriveService($this->client);
			}
		} else {
			$this->error_message = 'could not get connection';
		}
	}

	/*
	ASSET-SCOPE SEED REQUIRED FUNCTIONS
	(if they aren't relevant simply return true)

	finalizeUpload($filename)
	makePublic($filename)
	getExpiryURL($filename)
	getUploadParameters()
	*/

	public static function getRedirectMarkup($data=false) {
		$connections = CASHSystem::getSystemSettings('system_connections');
		
		if (isset($connections['com.google.drive'])) {
			$login_url = GoogleDriveSeed::getAuthorizationUrl(
				$connections['com.google.drive']['client_id'],
				$connections['com.google.drive']['redirect_uri'],
				array(
					'https://www.googleapis.com/auth/drive',
					'https://www.googleapis.com/auth/userinfo.email',
					'https://www.googleapis.com/auth/userinfo.profile'
				));
			$return_markup = '<h4>Connect to Google Drive</h4>'
						   . '<p>This will redirect you to a secure login at Google and bring you right back.</p>'
						   . '<a href="' . $login_url . '" class="button">Connect your Google Drive</a>';
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
							'access_expires' => $credentials_array['created'] + $credentials_array['expires_in'],
							'refresh_token'  => $credentials_array['refresh_token']
						)
					);
					if (!$result) {
						$settings_for_user = $new_connection->getAllConnectionsforUser();
						if (is_array($settings_for_user)) {
							foreach ($settings_for_user as $key => $connection_data) {
								if ($connection_data['name'] == $email_address . ' (Google Drive)') {
									$result = $connection_data['id'];
									break;
								}
							}
						}
					}
					if (isset($data['return_result_directly'])) {
						return $result;
					} else {
						if ($result) {
							AdminHelper::formSuccess('Success. Connection added. You\'ll see it below.','/settings/connections/');
						} else {
							AdminHelper::formFailure('Error. Something just didn\'t work right.','/settings/connections/');
						}
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
		$client->setUseObjects(false);
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

	public function getUploadParameters() {
		$current_auth = json_decode($this->access_token,true);
		$return_array = array(
			'connection_type'       => $this->settings_type,
			'google_drive_app_id'   => $this->app_id, 
			'google_drive_key'      => $this->api_key,
			'connection_auth_token' => $current_auth['access_token'],
			'connection_auth_user'  => $this->email_address
		);
		return $return_array;
	}

	public function getExpiryURL($filename) {
		$file = $this->drive_service->files->get($filename);

		// funny and not-so-documented google drive trick. you don't have to use an authorization
		// header for downloadUrl access if you have a valid oauth token. you can append an 
		// access_token parameter to the url and get access — and with tokens expiring AT MOST
		// one hour from now it makes the download URL nice and expiry. neat.
		$full_token = json_decode($this->access_token,true);
		$authorized_url = $file['downloadUrl'] . '&access_token=' . $full_token['access_token'];
		return $authorized_url;
	}

	public function makePublic($filename) {
		$permission = new Google_Permission();
		$permission->setValue('');
		$permission->setType('anyone');
		$permission->setRole('reader');

		$this->drive_service->permissions->insert($filename,$permission);

		// the "official" webContentLink requires some form of auth, even for public. dumb
		//
		// $file = $this->drive_service->files->get($filename);
		// return $file['webContentLink'];

		$public_link = 'https://drive.google.com/uc?export=download&id=' . $filename;
		return $public_link;
	}

	// required for Asset seeds
	public function finalizeUpload($filename) {return true;}
	
} // END class
?>
