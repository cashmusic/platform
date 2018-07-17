<?php

namespace Analog\Handler;

/**
 * Post the log info to the specified Slack channel through Slack's Slackbot.
 *
 * Usage:
 *
 *     $team = 'team-subdomain';
 *     $token = 'slackbot token';
 *     $channel = 'slack-channel';
 *     Analog::handler (Analog\Handler\Slackbot::init ($team, $token, $channel));
 *
 * Note: Requires cURL.
 */
class Slackbot {
	public static function init ($team, $token, $channel) {
		return function ($info) use ($team, $token, $channel) {
			if (! extension_loaded ('curl')) {
				throw new \LogicException ('CURL extension not loaded.');
			}
			
			$url = sprintf (
				'https://%s.slack.com/services/hooks/slackbot?token=%s&channel=%s',
				$team,
				$token,
				$channel
			);
			
			$msg = sprintf (
				'%s (%d): %s',
				$info['machine'],
				$info['level'],
				$info['message']
			);

			$ch = curl_init ();
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_POST, 1);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $msg);
			curl_exec ($ch);
			curl_close ($ch);
		};
	}
}