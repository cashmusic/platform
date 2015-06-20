<?php
/**
 * Twitter library wrapper and public feed fetcher
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
 * This file is generously sponsored by 'Sweetest tongue has sharpest tooth'
 *
 **/
class TwitterSeed extends SeedBase {
	protected $twitter;
	protected $oauth_token;
	protected $oauth_token_secret;
	protected $client_id;
	protected $client_secret;

	public function __construct($user_id=false,$connection_id=false) {
		$this->settings_type = 'com.twitter';
		$this->user_id = $user_id;
		$this->primeCache();
		if ($user_id && !$connection_id) {
			$connection = new CASHConnection($user_id);
			$result = $connection->getConnectionsByType($this->settings_type);
			if (is_array($result)) {
				$connection_id = $result[0]['id'];
			}
		}
		$this->connection_id = $connection_id;
		if ($user_id && $connection_id) {
			$this->connectDB();
			if ($this->getCASHConnection()) {
				$token = $this->settings->getSetting('token');
				$this->oauth_token = $token['oauth_token'];
				$this->oauth_token_secret = $token['oauth_token_secret'];

				if (!$this->oauth_token && !$this->oauth_token_secret) {
					$this->error_message = 'no API key found';
				} else {
					require_once(CASH_PLATFORM_ROOT.'/lib/twitter/OAuth.php');
					require_once(CASH_PLATFORM_ROOT.'/lib/twitter/twitteroauth.php');

					$connections = CASHSystem::getSystemSettings('system_connections');
					$this->client_id = $connections['com.twitter']['client_id'];
					$this->client_secret = $connections['com.twitter']['client_secret'];

					$this->twitter = new TwitterOAuth(
						$this->client_id,
						$this->client_secret,
						$this->oauth_token,
						$this->oauth_token_secret
					);
				}
			} else {
				$this->error_message = 'could not get connection';
			}
		}
	}

	public static function getRedirectMarkup($data=false) {
		$connections = CASHSystem::getSystemSettings('system_connections');

		if (isset($connections['com.twitter'])) {
			require_once(CASH_PLATFORM_ROOT.'/lib/twitter/OAuth.php');
			require_once(CASH_PLATFORM_ROOT.'/lib/twitter/twitteroauth.php');

			$twitter = new TwitterOAuth($connections['com.twitter']['client_id'], $connections['com.twitter']['client_secret']);
			$temporary_credentials = $twitter->getRequestToken($connections['com.twitter']['redirect_uri']);

			// store temporary credentials in the session for return
			$session_request = new CASHRequest();
			$session_request->sessionSet('twitter_temporary_credentials',$temporary_credentials);

			$login_url = $twitter->getAuthorizeURL($temporary_credentials, FALSE);

			$return_markup = '<h4>Twitter</h4>'
						   . '<p>This will redirect you to a secure login on twitter.com and bring you right back.</p>'
						   . '<a href="' . $login_url . '" class="button">Connect your Twitter account</a>';
			return $return_markup;
		} else {
			return 'Please add default twitter app credentials.';
		}
	}

	public static function handleRedirectReturn($data=false) {
		if (isset($data['error'])) {
			return 'There was an error. (general) Please try again.';
		} else {
			$connections = CASHSystem::getSystemSettings('system_connections');

			require_once(CASH_PLATFORM_ROOT.'/lib/twitter/OAuth.php');
			require_once(CASH_PLATFORM_ROOT.'/lib/twitter/twitteroauth.php');

			$temporary_credentials = AdminHelper::getPersistentData('twitter_temporary_credentials');

			$twitter = new TwitterOAuth(
				$connections['com.twitter']['client_id'],
				$connections['com.teitter']['client_secret'],
				$temporary_credentials['oauth_token'],
				$temporary_credentials['oauth_token_secret']
			);
			$access_token = $twitter->getAccessToken($_REQUEST['oauth_verifier']);


			if ($twitter->http_code == 200) {
				// we can safely assume (AdminHelper::getPersistentData('cash_effective_user') as the OAuth
				// calls would only happen in the admin. If this changes we can fuck around with it later.
				$new_connection = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
				$result = $new_connection->setSettings(
					'@' . $access_token['screen_name'] . ' (Twitter)',
					'com.twitter',
					array(
						'token' => $access_token
					)
				);
				if ($result) {
					AdminHelper::formSuccess('Success. Connection added. You\'ll see it in your list of connections.','/settings/connections/');
				} else {
					AdminHelper::formFailure('Error. Could not save connection.','/settings/connections/');
				}
			} else {
				AdminHelper::formFailure('Error. Problem communicating with Twitter','/settings/connections/');
			}
		}
	}

	// check the cache before requesting new data. 10 minute shelf life in there
	protected function getCachedAPIResponse($endpoint,$params) {
		$data_name = http_build_query($params, '', '-');
		$data = $this->getCacheData($this->settings_type,$data_name);

		if (!$data && $this->twitter) {
			$data = $this->twitter->get($endpoint,$params);
			if (!$data) {
				$data = $this->getCacheData($this->settings_type,$data_name,true);
			} else {
				foreach ($data as $tweet) {
					// add formatted time to tweet
					$tweet->formatted_created_at = CASHSystem::formatTimeAgo($tweet->created_at);
					// handle url links
					$twitterstatus = true;
					if (isset($tweet->entities)) {
						if (isset($tweet->entities->urls)) {
							$twitterstatus = $tweet->entities->urls;
						}
					}
					$tweet->text = CASHSystem::linkifyText($tweet->text,$twitterstatus);
					// add media collections
					// handle twitter photos
					if (isset($tweet->extended_entities)) {
						if (is_object($tweet->extended_entities)) {
							if (is_array($tweet->extended_entities->media)) {
								$tweet->photos = array();
								foreach ($tweet->extended_entities->media as $m) {
									$tweet->photos[] = $m;
								}
							}
						}
					}
					// handle youtube videos
					if (isset($tweet->entities)) {
						if (is_object($tweet->entities)) {
							if (is_array($tweet->entities->urls)) {
								$tweet->iframes = array();
								foreach ($tweet->entities->urls as $u) {
									if (strpos($u->expanded_url,'youtube.com') > 0) {
										$parsed_url = parse_url($u->expanded_url);
										$query_array = array();
										parse_str($parsed_url['query'],$query_array);
										if (isset($query_array['v'])) {
											$tweet->iframes[] = array('iframe_url' => '//www.youtube.com/embed/' . $query_array['v']);
											// <iframe src="//www.youtube.com/embed/dOy7vPwEtCw" frameborder="0" allowfullscreen></iframe>
										}
									}
								}
							}
						}
					}
				}
				$this->setCacheData($this->settings_type,$data_name,$data);
			}
		}
		return $data;
	}

	public function getUser($username,$extended_detail=false) {
		$username = str_replace('@','',$username);
		$endoint_url = 'https://api.twitter.com/1.1/users/show.json?screen_name=' . $username;
		$user_data = $this->getCachedAPIResponse('users/show',
			array(
				'screen_name' => $username
			)
		);
		if ($user_data && !$extended_detail) {
			// let's trim out some of the things we don't need
			unset($user_data['status']);
			unset($user_data['follow_request_sent']);
			unset($user_data['profile_background_color']);
			unset($user_data['profile_background_tile']);
			unset($user_data['profile_sidebar_fill_color']);
			unset($user_data['notifications']);
			unset($user_data['default_profile_image']);
			unset($user_data['show_all_inline_media']);
			unset($user_data['profile_sidebar_border_color']);
			unset($user_data['following']);
			unset($user_data['is_translator']);
			unset($user_data['profile_use_background_image']);
			unset($user_data['profile_text_color']);
			unset($user_data['profile_background_image_url']);
			unset($user_data['profile_link_color']);
		}
		return $user_data;
	}

	public function getUserFeed($username,$exclude_replies=true,$count=200,$filtertype=false,$filter=false) {
		if ($username) {
			// twitter does some filtering (RTs and replies, if specified) but the count variable comes
			// before that filtering. we need to jack this way up, then cut it down after the fact
			$working_count = ($count * 2) + 100;

			$feed_data = $this->getCachedAPIResponse('statuses/user_timeline',
				array(
					'screen_name' => $username,
					'exclude_replies' => $exclude_replies,
					'count' => $working_count,
					'trim_user' => false,
					'include_rts' => false
				)
			);

			/*
			$tweet->text = CASHSystem::linkifyText($tweet->text,true);
			$tweet->formatted_created_at = CASHSystem::formatTimeAgo($tweet->created_at);
			*/

			if (is_array($feed_data) && $filter) {
				$filter = strtolower($filter);
				$return_feed = array();
				if (is_array($feed_data)) {
					foreach ($feed_data as $tweet) {
						if ($filtertype == 'beginwith') {
							if (strrpos(strtolower($tweet->text),$filter) === 0) {
								$return_feed[] = $tweet;
							}
						} else {
							if (strrpos(strtolower($tweet->text),$filter) !== false) {
								$return_feed[] = $tweet;
							}
						}
					}
					$feed_data = $return_feed;
				}
			}

			if (is_array($feed_data)) {
				// trim down to the requested number of tweets
				$feed_data = array_slice($feed_data, 0, $count);
			}

			return $feed_data;
		} else {
			return false;
		}
	}
} // END class
?>
