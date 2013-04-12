<?php
/**
 * Mailchimp seed to connect to the mailchimp 1.3 API
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
 * This file is generously sponsored by TTSCC - http://tts.cc - This code kills fascists
 *
 **/
class MandrillSeed extends SeedBase {
	private $api;
	public $key, $error_code=false, $error_message=false;

	public function __construct($user_id, $connection_id) {
		$this->settings_type = 'com.mandrillapp';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		if ($this->getCASHConnection()) {
			if (!$this->key) {
				$this->error_message = 'no API key found';
			} else {
				require_once(CASH_PLATFORM_ROOT.'/lib/mandrill/Mandrill.php');
				$this->key = $this->settings->getSetting('key');
				$this->api = new Mandrill($this->key);
			}
		} else {
			$this->error_message = 'could not get connection';
		}
	}

	public static function getRedirectMarkup($data=false) {
		$connections = CASHSystem::getSystemSettings('system_connections');
		
		if (isset($connections['com.mandrillapp'])) {
			require_once(CASH_PLATFORM_ROOT.'/lib/mandrill/Mandrill.php');
			$login_url = 'http://mandrillapp.com/api-auth/?id=' 
					   . $connections['com.mandrillapp']['app_authentication_id'] 
					   . '&redirect_url=' 
					   . urlencode($connections['com.mandrillapp']['redirect_uri']);

			$return_markup = '<h4>Connect to Mandrill</h4>'
						   . '<p>This will redirect you to a secure login on mandrillapp.com and bring you right back.</p>'
						   . '<a href="' . $login_url . '" class="button">Connect your MailChimp account</a>';
			return $return_markup;
		} else {
			return 'Please add default mandrill app credentials.';
		}
	}

	public static function handleRedirectReturn($data=false) {
		if (!isset($data['key'])) {
			return 'There was an error. (general) Please try again.';
		} else {
			require_once(CASH_PLATFORM_ROOT.'/lib/mandrill/Mandrill.php');
			$m = new Mandrill($data['key']);
			$user_info = $m->getUserInfo();
			$username  = $user_info['username'];

			// we can safely assume (AdminHelper::getPersistentData('cash_effective_user') as the OAuth 
			// calls would only happen in the admin. If this changes we can fuck around with it later.
			$new_connection = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
			$result = $new_connection->setSettings(
				$username . ' (Mandrill)',
				'com.mandrillapp',
				array(
					'key' => $data['key']
				)
			);
			if (!$result) {
				return 'There was an error. (adding the connection) Please try again.';
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
		}
	}

	private function handleError() {
		if ($this->api->errorCode) {
			$this->error_code = $this->api->errorCode;
			$this->error_message = $this->api->errorMessage;
		}
	}

	// http://apidocs.mailchimp.com/api/1.3/lists.func.php
	public function lists() {
		$lists = $this->api->lists();
		$this->handleError();
		if ($this->error_code !== false) {
			return false;
		} else {
			return $lists;
		}
	}
} // END class
?>
