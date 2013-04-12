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
class MailchimpSeed extends SeedBase {
	private $api;
	public $url, $key, $list_id, $error_code=false, $error_message=false;

	public function __construct($user_id, $connection_id) {
		$this->settings_type = 'com.mailchimp';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		if ($this->getCASHConnection()) {
			require_once(CASH_PLATFORM_ROOT.'/lib/mailchimp/MCAPI.class.php');
			$this->key      = $this->settings->getSetting('key');
			$this->list_id  = $this->settings->getSetting('list');
			$this->api      = new MCAPI($this->key);
			
			if ($this->key) {
				if (strpos($this->key,'-') === false) {
					$this->error_message = 'invalid API key';
				} else {
					$parts = explode("-", $this->key);
					$this->url = 'http://' . $parts[1] . '.api.mailchimp.com/1.3/';
				}
			} else {
				$this->error_message = 'no API key found';
			}
		} else {
			$this->error_message = 'could not get connection';
		}
	}

	public static function getRedirectMarkup($data=false) {
		$connections = CASHSystem::getSystemSettings('system_connections');
		
		if (isset($connections['com.mailchimp'])) {
			require_once(CASH_PLATFORM_ROOT.'/lib/oauth2/OAuth2Client.php');
			require_once(CASH_PLATFORM_ROOT.'/lib/oauth2/OAuth2Exception.php');
			require_once(CASH_PLATFORM_ROOT.'/lib/mailchimp/MC_OAuth2Client.php');
			$auth = new MC_OAuth2Client(
				array(
					'redirect_uri'  => $connections['com.mailchimp']['redirect_uri'],
					'client_id'     => $connections['com.mailchimp']['client_id'],
					'client_secret' => $connections['com.mailchimp']['client_secret']
				)
			);
			$login_url = $auth->getLoginUri();

			$return_markup = '<h4>Connect to MailChimp</h4>'
						   . '<p>This will redirect you to a secure login on mailchimp.com and bring you right back.</p>'
						   . '<a href="' . $login_url . '" class="button">Connect your MailChimp account</a>';
			return $return_markup;
		} else {
			return 'Please add default mailchimp app credentials.';
		}
	}

	public static function handleRedirectReturn($data=false) {
		if (isset($data['error'])) {
			return 'There was an error. (general) Please try again.';
		} else {
			$connections = CASHSystem::getSystemSettings('system_connections');

			require_once(CASH_PLATFORM_ROOT.'/lib/oauth2/OAuth2Client.php');
			require_once(CASH_PLATFORM_ROOT.'/lib/oauth2/OAuth2Exception.php');
			require_once(CASH_PLATFORM_ROOT.'/lib/mailchimp/MC_OAuth2Client.php');
			$oauth_options = array(
				'redirect_uri'  => $connections['com.mailchimp']['redirect_uri'],
				'client_id'     => $connections['com.mailchimp']['client_id'],
				'client_secret' => $connections['com.mailchimp']['client_secret'],
				'code'          => $data['code']
			);
			$client = new MC_OAuth2Client($oauth_options);
			$session = $client->getSession();
			if ($session) {
				require_once(CASH_PLATFORM_ROOT.'/lib/mailchimp/MCAPI.class.php');
				$cn = new MC_OAuth2Client($oauth_options);
        		$cn->setSession($session,false);
        		$odata = $cn->api('metadata', 'GET');
        		$access_token = $session['access_token'];
        		$api_key = $session['access_token'] . '-' . $odata['dc'];

        		$api = new MCAPI($api_key);
				$api->useSecure(true);
				$lists = $api->lists('', 0, 50);

				$return_markup = '<h4>Connect to MailChimp</h4>'
							   . '<p>Now just choose a list and save the connection.</p>'
							   . '<form accept-charset="UTF-8" method="post" action="">'
							   . '<input type="hidden" name="dosettingsadd" value="makeitso" />'
							   . '<input id="connection_name_input" type="hidden" name="settings_name" value="(MailChimp list)" />'
							   . '<input type="hidden" name="settings_type" value="com.mailchimp" />'
							   . '<input type="hidden" name="key" value="' . $api_key . '" />'
							   . '<label for="list">Choose a list to connect to:</label>'
							   . '<select id="list_select" name="list">';
				$selected = ' selected="selected"';
				$list_name = false;
				foreach ($lists['data'] as $list) {
					if ($selected) {
						$list_name = $list['name'];
					}
					$return_markup .= '<option value="' . $list['id'] . '"' . $selected . '>' . $list['name'] . '</option>';
					$selected = false;
				}
				$return_markup .= '</select><br /><br />'
								. '<div><input class="button" type="submit" value="Add The Connection" /></div>'
								. '</form>'
								. '<script type="text/javascript">'
								. '$("#connection_name_input").val("' . $list_name . ' (MailChimp)");'
								. '$("#list_select").change(function() {'
								. '	var newvalue = this.options[this.selectedIndex].text + " (MailChimp)";'
								. '	$("#connection_name_input").val(newvalue);'
								. '});'
								. '</script>';
				return $return_markup;
			} else {
				return 'There was an error. (session) Please try again.';
			}
		}
	}

	private function handleError() {
		if ($this->api->errorCode) {
			$this->error_code = $this->api->errorCode;
			$this->error_message = $this->api->errorMessage;
		}
	}

	public function getListId() {
		return $this->list_id;
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
	// http://apidocs.mailchimp.com/api/1.3/listwebhooks.func.php
	public function listWebhooks() {
		$webhooks = $this->api->listWebhooks($this->list_id);
		$this->handleError();
		if ($this->error_code !== false) {
			return false;
		} else {
			return $webhooks;
		}
	}
	// http://apidocs.mailchimp.com/api/1.3/listwebhookadd.func.php
	public function listWebhookAdd($url, $actions=null, $sources=null) {
		$this->api->listWebhookAdd($this->list_id, $url, $actions, $sources);
		$this->handleError();
		if ($this->error_code !== false) {
			return false;
		} else {
			return true;
		}
	}
	// http://apidocs.mailchimp.com/api/1.3/listwebhookdel.func.php
	public function listWebhookDel($url) {
		$this->api->listWebhookDel($this->list_id, $url);
		$this->handleError();
		if ($this->error_code !== false) {
			return false;
		} else {
			return true;
		}
	}
	// http://apidocs.mailchimp.com/api/1.3/listmembers.func.php
	public function listMembers() {
		$page    = 0;
		$max     = 5000;
		$since   = null;
		$members = $this->api->listMembers($this->list_id, 'subscribed', $since, $page, $max);
		$this->handleError();
		if ($this->error_code !== false) {
			return false;
		} else {
			return $members;
		}
	}
	// http://apidocs.mailchimp.com/api/1.3/listsubscribe.func.php
	public function listSubscribe($email, $merge_vars=null, $email_type=null, $double_optin=true, $update_existing=false, $replace_interests=true, $send_welcome=false) {
		$this->api->listSubscribe($this->list_id, $email, $merge_vars, $email_type, $double_optin, $update_existing, $replace_interests, $send_welcome);
		$this->handleError();
		if ($this->error_code !== false) {
			return false;
		} else {
			return true;
		}
	}
	// http://apidocs.mailchimp.com/api/1.3/listunsubscribe.func.php
	public function listUnsubscribe($email) {
		$delete       = 0;
		$send_goodbye = 1;
		$send_notify  = 1;
		$this->api->listUnsubscribe($this->list_id, $email, $delete, $send_goodbye, $send_notify);
		$this->handleError();
		if ($this->error_code !== false) {
			return false;
		} else {
			return true;
		}
	}
} // END class
?>
