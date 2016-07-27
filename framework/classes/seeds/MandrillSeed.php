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
	public $api_key, $error_code=false, $error_message=false;

	public function __construct($user_id, $connection_id=false) {

		if(CASH_DEBUG) {
			error_log("Called MandrillSeed with user id $user_id, connection id $connection_id");
		}
		$this->settings_type = 'com.mandrillapp';
		$this->user_id = $user_id;
		
		// if there's no $connection_id, we'll just default to CASH's key
		
		$this->connection_id = $connection_id;

		if ($this->getCASHConnection()) {

			// check if the user has a connection for this service
			if (!$this->api_key = $this->settings->getSetting('api_key')) {
				return false;
			}

			$this->api = new Mandrill($this->api_key);

		} else {
			// if not let's default to the system settings
			$connections = CASHSystem::getSystemSettings('system_connections');

			if (CASH_DEBUG) {
				error_log(
					"Default Mandrill connection stuff\n".
					print_r($connections['com.mandrillapp'], true)
				);
			}

			if (isset($connections['com.mandrillapp']['api_key'])) {
				$this->api_key = $connections['com.mandrillapp']['api_key'];

				$this->api = new Mandrill($this->api_key);
				echo print_r(get_class_methods($this->api), true);
				return false;
			} else {
				$this->error_message = 'no API key found';
				return false;
			}
		}
	}

	public static function getRedirectMarkup($data=false) {
		$connections = CASHSystem::getSystemSettings('system_connections');

		// I don't like using ADMIN_WWW_BASE_PATH below, but as this call is always called inside the
		// admin I'm just going to do it. Without the full path in the form this gets all fucky
		// and that's no bueno.

		if (isset($connections['com.mandrillapp'])) {
			$return_markup = '<h4>Mandrill by Mailchimp</h4>'
				. '<p>You\'ll need a Mailchimp with Mandrill API key to connect properly. Mandrill is a paid add-on for Mailchimp. Read more <a href="http://kb.mailchimp.com/mandrill/add-or-remove-mandrill">here</a>.</p>'
				. '<form accept-charset="UTF-8" method="post" id="mandrill_connection_form" action="' . $data . '">'
//				. '<input type="hidden" name="dosettingsadd" value="makeitso" />'
//				. '<input type="hidden" name="permission_type" value="accelerated" />'
				. '<input id="connection_name_input" type="hidden" name="settings_name" value="(Mandrill)" />'
//				. '<input type="hidden" name="settings_type" value="com.mandrillapp" />'
				. '<label for="merchant_email">Your Mandrill API key:</label>'
				. '<input type="text" name="api_key" id="api_key" value="" />'
				. '<br />'
				. '<div><input class="button" type="submit" value="Add The Connection" /></div>'
				. '</form>';

			return $return_markup;
		} else {
			return 'Please add default Mandrill API credentials.';
		}
	}

	public static function handleRedirectReturn($data=false) {

		if (!isset($data['api_key'])) {
			return 'There was an error. (general) Please try again.';
		} else {

			$mandrill = new Mandrill($data['api_key']);

			// we can safely assume (AdminHelper::getPersistentData('cash_effective_user') as the OAuth
			// calls would only happen in the admin. If this changes we can fuck around with it later.
			$new_connection = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
			$result = $new_connection->setSettings(
				$data['api_key'] . ' (Mandrill)',
				'com.mandrillapp',
				array(
					'key' => $data['api_key']
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
			$mandrill->call('webhooks/add', array("url"=>$webhook_api_url,"events"=>array('hard_bounce','soft_bounce','open','click','spam','unsub','reject')));

			return array(
				'id' => $result,
				'name' => $data['api_key'] . ' (Mandrill)',
				'type' => 'com.mandrillapp'
			);
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
			"auto_text" => false,
			"auto_html" => false,
			"inline_css" => true,
			"url_strip_qs" => null,
			"preserve_recipients" => null,
			"bcc_address" => null,
			"tracking_domain" => null,
			"signing_domain" => null,
			"merge" => true,
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
