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

namespace CASHMusic\Seeds;

use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Core\SeedBase;
use CASHMusic\Core\CASHConnection;
use CASHMusic\Admin\AdminHelper;

use Mandrill;

class MandrillSeed extends SeedBase {
	private $api;
	public $api_key, $api_email, $error_code=false, $error_message=false;

	public function __construct($user_id, $connection_id=false) {

		$this->settings_type = 'com.mandrillapp';
		$this->user_id = $user_id;

		// if there's no $connection_id, we'll just default to CASH's key

		$this->connection_id = $connection_id;

		if ($this->getCASHConnection()) {

			// check if the user has a connection for this service
			if (!$this->api_key = $this->settings->getSetting('api_key')) {
                $connections = CASHSystem::getSystemSettings('system_connections');

                if (isset($connections['com.mandrillapp']['api_key'])) {
                    $this->api_key = $connections['com.mandrillapp']['api_key'];
                } else {
                    $this->error_message = 'no API key found';
                    return false;
                }
			}

			$settings_api_email = $this->settings->getSetting('api_email');
            $cash_settings = CASHSystem::getSystemSettings();

            $this->api_email = isset($settings_api_email) ? $settings_api_email : $cash_settings['systememail'];

		} else {
            // if not let's default to the system settings
            $connections = CASHSystem::getSystemSettings('system_connections');

            if (isset($connections['com.mandrillapp']['api_key'])) {
                $this->api_key = $connections['com.mandrillapp']['api_key'];
            } else {
                $this->error_message = 'No Mandrill API key found';
                return false;
            }

            $cash_settings = CASHSystem::getSystemSettings();

            $this->api_email = "info@cashmusic.org"; //$cash_settings['systememail'];

		}

        $this->api = new Mandrill($this->api_key);

	}

	public static function getRedirectMarkup($data=false, $admin_helper=false) {
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
				. '<input type="text" name="api_key" id="api_key" value="" class="required" />'
				. '<label for="merchant_email">Mandrill from email:</label>'
				. '<input type="text" name="api_email" id="api_email" value="" class="required" />'
				. '<br />'
				. '<div><input class="button" type="submit" value="Add The Connection" /></div>'
				. '</form>';

			return $return_markup;
		} else {
			return 'Please add default Mandrill API credentials.';
		}
	}

	public static function handleRedirectReturn($effective_user=false, $request=false, $admin_helper=false) {

		if (!isset($request['api_key'])) {
			return 'There was an error. (general) Please try again.';
		} else {

			$mandrill = new Mandrill($request['api_key']);
			// we can safely assume (AdminHelper::getPersistentData('cash_effective_user') as the OAuth
			// calls would only happen in the admin. If this changes we can fuck around with it later.
			$new_connection = new CASHConnection($admin_helper->getPersistentData('cash_effective_user'));
			$result = $new_connection->setSettings(
                $request['api_email'] . ' (Mandrill)',
				'com.mandrillapp',
				array(
					'api_key' => $request['api_key'],
					'api_email' => $request['api_email']
				)
			);
			if (!$result) {
				return 'There was an error. (adding the connection) Please try again.';
			}
			// set up webhooks
			$api_credentials = CASHSystem::getAPICredentials();
            error_log("these credentials " . json_encode($api_credentials));
			$webhook_api_url = CASH_API_URL . '/verbose/people/processwebhook/origin/com.mandrillapp/api_key/' . $api_credentials['api_key'];

			//$m->webhooksDelete($webhook_api_url); // remove duplicate webhooks
			//$m->webhooksAdd($webhook_api_url,array('send','hard_bounce','soft_bounce','open','click','spam','unsub','reject')); // add it, all events
			$mandrill->call('webhooks/add', array("url"=>$webhook_api_url,"events"=>array('hard_bounce','soft_bounce','open','click','spam','unsub','reject')));

			return array(
				'id' => $result,
				'name' => $request['api_email'] . ' (Mandrill)',
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
	public function send($subject,$message_txt,$message_html,$from_address,$from_name,$sender_address,$recipients,$metadata=null,$global_merge_vars=null,$merge_vars=null,$tags=null) {

		$unsubscribe_link = '';
		if ($metadata) {
			if (isset($metadata['list_id'])) {
				$unsubscribe_link = '<a style="margin:0;padding:0;" href="' .
					CASH_PUBLIC_URL .
					'/request/html?cash_request_type=people&cash_action=removeaddress&list_id=' .
					$metadata['list_id'] .
					'&address={{email_address}}' .
					'">Unsubscribe</a>';
			}
			$message_html = str_replace('$UNSUBSCRIBE$','*|UNSUBSCRIBELINK|*',$message_html);
		} else {
            $message_html = str_replace('$UNSUBSCRIBE$','',$message_html);
		}

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

		// this should be the mandrill "sender email", else default to system
		$email_settings = CASHSystem::getDefaultEmail(true);
		$sender = CASHSystem::parseEmailAddress($email_settings['systememail']);
		$sender_email = $this->api_email ? $this->api_email : key($sender);

		$merged_vars = [];

		if (!empty($merge_vars)) {
			if (!empty($recipient_merge_vars)) {
				foreach($merge_vars as $merge_var) {
					$value = $merge_var['rcpt'];

					$unsubscribe_vars = CASHSystem::searchArrayMulti($recipient_merge_vars, "rcpt", $value);

                    $merge_var['vars'] = array_merge($unsubscribe_vars[0]['vars'], $merge_var['vars']);
					$merged_vars[] = $merge_var;
				}

				$merge_vars = $merged_vars;
			}
		} else {
			$merge_vars = $recipient_merge_vars;
		}

		if ($this->user_id) {
			// get current user details for email
			$user_request = new CASHRequest(
				array(
					'cash_request_type' => 'people',
					'cash_action' => 'getuser',
					'user_id' => $this->user_id
				)
			);

			$user_details = $user_request->response['payload'];

			// make sure we're not overriding the name that's passed manually
			if (!$from_name) {
				$from_name = $user_details['display_name'];
				$from_address = $user_details['email_address'];

				if ($user_details['display_name'] == 'Anonymous' || !$user_details['display_name']) {
					$from_name = $user_details['email_address'];
				}
			}

		} else {
			// we're testing so let's just fake this for now
			$user_details['email_address'] = 'info@cashmusic.org';
			$user_details['display_name'] = 'Testing CASH Mailer';
		}

		if (empty($global_merge_vars) && empty($merge_vars)) {
			$merge = false;
		} else {
			$merge = true;
		}

		$message = array(
			"html" => $message_html,
			"text" => $message_txt,
			"subject" => $subject,
			'headers' => array('Reply-To' => $from_address),
			"from_email" => $sender_email,
			"from_name" => $from_name,
			"to" => $recipients,
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
			"merge" => $merge,
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

		$result = $this->api->call('messages/send', array("message" => $message, "async" => true));

		return $result;
	}

} // END class
?>
