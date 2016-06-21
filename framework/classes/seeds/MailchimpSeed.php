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
 *
 *
 * TODO: UPGRADE TO MAILCHIMP v3.0 API >>> https://github.com/drewm/mailchimp-api/
 *
 *
 *
 **/

require_once(CASH_PLATFORM_ROOT . '/lib/mailchimp/MailChimp.php');

use \DrewM\MailChimp\MailChimp as MailChimp;

class MailchimpSeed extends SeedBase {
	private $api;
	public $url, $key, $list_id, $error_code=false, $error_message=false;

	public function __construct($user_id, $connection_id) {
		$this->settings_type = 'com.mailchimp';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		if ($this->getCASHConnection()) {

			$this->key      = $this->settings->getSetting('key');
			$this->list_id  = $this->settings->getSetting('list');
			$this->api      = new MailChimp($this->key);

			if (!$this->key) {
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
			require_once(CASH_PLATFORM_ROOT . '/lib/mailchimp/MC_OAuth2Client.php');
			$auth = new MC_OAuth2Client(
				array(
					'redirect_uri'  => $connections['com.mailchimp']['redirect_uri'],
					'client_id'     => $connections['com.mailchimp']['client_id'],
					'client_secret' => $connections['com.mailchimp']['client_secret']
				)
			);
			$login_url = $auth->getLoginUri();

			$return_markup = '<h4>MailChimp</h4>'
						   . '<p>This will redirect you to a secure login on mailchimp.com and bring you right back.</p>'
						   . '<a href="' . $login_url . '" class="button">Connect your MailChimp account</a>';
			return $return_markup;
		} else {
			return 'Please add default mailchimp app credentials.';
		}
	}

	public static function handleRedirectReturn($data=false) {

		if (isset($data['error'])) {
			return 'There was an error. (general) Please try again. More: ' . $data['error'];
		} else {
			$connections = CASHSystem::getSystemSettings('system_connections');

			require_once(CASH_PLATFORM_ROOT.'/lib/oauth2/OAuth2Client.php');
			require_once(CASH_PLATFORM_ROOT.'/lib/oauth2/OAuth2Exception.php');
			require_once(CASH_PLATFORM_ROOT . '/lib/mailchimp/MC_OAuth2Client.php');
			$oauth_options = array(
				'redirect_uri'  => $connections['com.mailchimp']['redirect_uri'],
				'client_id'     => $connections['com.mailchimp']['client_id'],
				'client_secret' => $connections['com.mailchimp']['client_secret'],
				'code'          => $data['code']
			);


			$client = new MC_OAuth2Client($oauth_options);
			$session = $client->getSession();
			if ($session) {
				$cn = new MC_OAuth2Client($oauth_options);
        		$cn->setSession($session,false);
        		$odata = $cn->api('metadata', 'GET');
        		$access_token = $session['access_token'];
        		$api_key = $session['access_token'] . '-' . $odata['dc'];

        		$api = new MailChimp($api_key);
        		$lists = $api->get('lists');

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

				foreach ($lists['lists'] as $list) {

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

	public static function postConnection($data=false) {
		if (isset($data['settings_name']) && isset($data['settings_id']) && isset($data['user_id'])) {
			$list_request = new CASHRequest(
				array(
					'cash_request_type' => 'people',
					'cash_action' => 'addlist',
					'name' => $data['settings_name'],
					'description' => 'Automatically added for MailChimp list on ' . date("F j, Y"),
					'user_id' => $data['user_id'],
					'connection_id' => $data['settings_id']
				)
			);

			$data['settings_id'];

			$mailchimp = new MailchimpSeed($data['user_id'], $data['settings_id']);
			$list_members = $mailchimp->listMembers();


			error_log( "member count " . count($list_members) );
		}
	}

	private function detectError($response) {
		//error_log( print_r($response, true) );
		// we need to override if we're on a local machine, because Mailchimp actually validates URLs we send it
		if (CASH_DEBUG == 'yes') return true;

		if (isset($response["status"])) {
			if ($response["status"] == "error") {
				$this->error_code = $response["code"];
				$this->error_message = $response["error"];
				return true;
			}
		}
		return false;
	}

	public function getListId() {
		return $this->list_id;
	}

	public function lists() {
		$response = $this->api->get('lists');

		if (empty($response)) {
			return false;
		} else {
			return $response;
		}
	}

	// http://apidocs.mailchimp.com/api/2.0/lists/webhooks.php
	public function listWebhooks() {

		$endpoint = 'lists/'.$this->list_id.'/webhooks';
		$response = $this->api->get($endpoint);

		if (empty($response)) {
			return false;
		} else {
			return $response;
		}
	}

	// http://apidocs.mailchimp.com/api/2.0/lists/webhook-add.php

	//TODO: in order to delete webhook we need to store an ID for it.
	public function listWebhookAdd($url) {
		// $options is reserved for expanding in the future for things
		// like 'actions' and 'sources'
		$endpoint = 'lists/'.$this->list_id.'/webhooks';
		$response = $this->api->post($endpoint,
			array(
				'url' => $url
			));

		if (empty($response)){
			return false;
		} else {
			return $response;
		}
	}

	// http://apidocs.mailchimp.com/api/2.0/lists/webhook-del.php
	public function listWebhookDel($url) {

		//TODO: not necessary but we may want to revisit in the future.
/*		$webhooks = $this->listWebhooks();

		if (empty($webhooks)) {
			return false;
		} else {
			return $response;
		}*/
	}

	public function listMembers($options=array()) {
		// $options reserved for options
		// this will only pull a max of 100 members. ultimately we should
		// switch over to the MC list export API here:
		// http://apidocs.mailchimp.com/export/1.0/list.func.php

		// we need to filter this to make sure we're not getting unsubscribed, etc and wasting bandwidth
		$options = array_merge($options, array('status' => 'subscribed'));

		$endpoint = 'lists/'.$this->list_id.'/members';
		$response = $this->api->get($endpoint, $options);
		if (empty($response['members'])) {
			return false;
		} else {
			return $response['members'];
		}
	}

	public function listMembersCount() {
		// $options reserved for options
		// this will only pull a max of 100 members. ultimately we should
		// switch over to the MC list export API here:
		// http://apidocs.mailchimp.com/export/1.0/list.func.php

		$endpoint = 'lists/'.$this->list_id.'/members';
		$response = $this->api->get($endpoint);
		if (empty($response['total_items'])) {
			return false;
		} else {
			return $response['total_items'];
		}
	}

	// http://apidocs.mailchimp.com/api/2.0/lists/subscribe.php
	public function listSubscribe($email) {
		// TODO: use $data for merge_vars
		$endpoint = 'lists/'.$this->list_id.'/members';
		$response = $this->api->post($endpoint,
			array(
				'status' => 'subscribed',
				'email_address' => $email
			));

		if (empty($response)) {
			return false;
		} else {
			return $response;
		}
	}

	// http://apidocs.mailchimp.com/api/2.0/lists/unsubscribe.php
	public function listUnsubscribe($email) {
		// TODO: use $options to set these:
		// $delete       = 0;
		// $send_goodbye = 1;
		// $send_notify  = 1;
		$email_hash = strtolower($email);

		$endpoint = 'lists/'.$this->list_id.'/members/'.md5($email_hash);
		$response = $this->api->delete($endpoint);
		return $response;
	}
} // END class
?>
