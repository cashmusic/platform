<?php
/**
 * Mailchimp seed to connect to the mailchimp 1.3 API
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class MailchimpSeed extends SeedBase {
	protected $api;
	public $url, $key;

	private function handleError() {
		if ($this->api->errorCode) {
			// TODO: throw a proper error
			echo "\n\tCode=".$this->api->errorCode;
			echo "\n\tMsg=".$this->api->errorMessage."\n";
		}
	}
	public function __construct($key) {
		$this->settings_type = 'com.mailchimp';
		$this->connectDB();
		$this->getCASHSettings();
		require_once(CASH_PLATFORM_ROOT.'/lib/mailchimp/MCAPI.class.php');
		$this->key = $key;
		$this->api = new MCAPI($key);
		$parts = explode("-", $key);
		$this->url = 'http://' . $parts[1] . '.api.mailchimp.com/1.3/';
	}

	// http://apidocs.mailchimp.com/api/1.3/lists.func.php
	public function lists() {
		$lists = $this->api->lists();
		$this->handleError();
		return $lists;
	}
	// http://apidocs.mailchimp.com/api/1.3/listwebhooks.func.php
	public function listWebhooks($list_id) {
		$webhooks = $this->api->listWebhooks($list_id);
		$this->handleError();
		return $webhooks;
	}
	// http://apidocs.mailchimp.com/api/1.3/listwebhookadd.func.php
	public function listWebhookAdd($list_id, $url, $actions, $sources) {
		$this->api->listWebhookAdd($list_id, $url, $actions, $sources);
		$this->handleError();
		return $this;
	}
	// http://apidocs.mailchimp.com/api/1.3/listwebhookdel.func.php
	public function listWebhookDel($list_id, $url) {
		$this->api->listWebhookDel($list_id, $url);
		$this->handleError();
		return $this;
	}
	// http://apidocs.mailchimp.com/api/1.3/listmembers.func.php
	public function listMembers($list_id) {
		$page    = 0;
		$max     = 5000;
		$since   = null;
		$members = $this->api->listMembers($list_id, 'subscribed', $since, $page, $max);
		$this->handleError();
		return $members;
	}
	// http://apidocs.mailchimp.com/api/1.3/listsubscribe.func.php
	public function listSubscribe($list_id, $email, $merge_vars=null, $email_type=null, $double_optin=true, $update_existing=false, $replace_interests=true, $send_welcome=false) {
		$this->api->listSubscribe( $list_id, $email, $merge_vars, $email_type, $double_optin, $update_existing, $replace_interests, $send_welcome);
		return $this;
	}
	// http://apidocs.mailchimp.com/api/1.3/listunsubscribe.func.php
	public function listUnsubscribe($list_id, $email) {
		$delete       = 0;
		$send_goodbye = 1;
		$send_notify  = 1;
		$api->listUnsubscribe( $list_id, $email, $delete, $send_goodbye, $send_notify);
		$this->handleError();
		return $this;
	}
} // END class
?>
