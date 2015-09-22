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
			$this->key = $this->settings->getSetting('key');
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

			$return_markup = '<h4>Mandrill</h4>'
						   . '<p>This will redirect you to a secure login on mandrillapp.com and bring you right back.</p>'
						   . '<a href="' . $login_url . '" class="button">Connect your Mandrill account</a>';
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
			// set up webhooks
			$api_credentials = CASHSystem::getAPICredentials();
			$webhook_api_url = CASH_API_URL . '/verbose/people/processwebhook/origin/com.mandrillapp/api_key/' . $api_credentials['api_key'];
			//$m->webhooksDelete($webhook_api_url); // remove duplicate webhooks
			//$m->webhooksAdd($webhook_api_url,array('send','hard_bounce','soft_bounce','open','click','spam','unsub','reject')); // add it, all events
			$m->call('webhooks/add', array("url"=>$webhook_api_url,"events"=>array('hard_bounce','soft_bounce','open','click','spam','unsub','reject')));

			if (isset($data['return_result_directly'])) {
				return $result;
			} else {
				if ($result) {
					AdminHelper::formSuccess('Success. Connection added. You\'ll see it in your list of connections.','/settings/connections/');
				} else {
					AdminHelper::formFailure('Error. Something just didn\'t work right.');
				}
			}
		}
	}

	// https://mandrillapp.com/api/docs/webhooks.html
	public function webhooksAdd($url, $events=array()) {
		$_params = array("url" => $url, "events" => $events);
		return $this->api->call('webhooks/add', $_params);
	}
	// https://mandrillapp.com/api/docs/webhooks.html
	public function webhooksDelete($url) {
		// we're not going straight-up on this one. instead we comb through all
		// webhooks and remove the ones matching the URL we specify
		$webhooks = $this->api->call('webhooks/list',array());
		if (is_array($webhooks)) {
			foreach ($webhooks as $webhook) {
				if (strtolower($webhook['url']) == strtolower($url)) {
					$this->api->call('webhooks/delete',array("id" => $webhook['id']));
				}
			}
		}
	}
	//https://mandrillapp.com/api/docs/messages.html#method=send
	public function send($subject,$message_txt,$message_html,$from_address,$from_name,$recipients,$metadata=null,$global_merge_vars=null,$merge_vars=null,$tags=null) {
		$unsubscribe_link = '';
		if ($metadata) {
			if (isset($metadata['list_id'])) {
				$unsubscribe_link = '<a href="' .
					CASH_PUBLIC_URL .
					'request/html?cash_request_type=people&cash_action=removeaddress&list_id=' .
					$metadata['list_id'] .
					'&address={{email_address}}' .
					'">Unsubscribe</a>';
			}
		}

		$message_html = str_replace('{{{unsubscribe_link}}}','*|UNSUBSCRIBELINK|*',$message_html);

		$recipient_metadata = null;
		$recipient_merge_vars = array();
		if (is_array($recipients)) {
			foreach ($recipients as &$recipient) {
				if (isset($recipient['metadata'])) {
					if (is_array($recipient['metadata'])) {
						if (!$recipient_metadata) {
							$recipient_metadata = array();
						}
						$recipient_metadata[] = array(
							"rcpt" => $recipient['email'],
							"values" => $recipient['metadata']
						);
						unset($recipient['metadata']);
					}
				}
				$recipient_merge_vars[] = array(
					"rcpt" => $recipient['email'],
					"vars" => array(
						array(
							"name" => "unsubscribelink",
							"content" => str_replace('{{email_address}}',urlencode($recipient['email']),$unsubscribe_link)
						)
					)
				);
			}
		}

		/*
		if ($global_merge_vars) {
			$global_merge_vars = json_encode($global_merge_vars);
		}

		if ($merge_vars) {
			$merge_vars = json_encode($merge_vars);
		}
		*/

		$message = array(
			"html" => $message_html,
			"text" => $message_txt,
			"subject" => $subject,
			"from_email" => $from_address,
			"from_name" => $from_name,
			"to" => $recipients,
			"headers" => null,
			"track_opens" => true,
			"track_clicks" => true,
			"auto_text" => true,
			"auto_html" => false,
			"inline_css" => true,
			"url_strip_qs" => null,
			"preserve_recipients" => null,
			"bcc_address" => null,
			"tracking_domain" => null,
			"signing_domain" => null,
			"merge" => null,
			"global_merge_vars" => $global_merge_vars,
			"merge_vars" => $merge_vars,
			"tags" => $tags,
			"google_analytics_domains" => null,
			"google_analytics_campaign" => null,
			"metadata" => $metadata,
			"recipient_metadata" => $recipient_metadata,
			"attachments" => null,
			"images" => null
		);

      return $this->api->call('messages/send', array("message" => $message, "async" => true));
	}

} // END class
?>
