<?php
/**
 * Twitter library wrapper and public feed fetcher
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
class TwitterSeed extends SeedBase {
	protected $twitter;

	public function __construct($user_id=false,$settings_id=false) {
		$this->settings_type = 'com.twitter';
		$this->user_id = $user_id;
		$this->settings_id = $settings_id;
		$this->primeCache();
		if ($user_id && $settings_id) {
			$this->connectDB();
			if ($this->getCASHSettings()) {
				// fire up an instance of the lib
			} else {
				// error out — potentially to special error message page.
			}
		}
	}
	
	public function getUserFeed($username,$exclude_replies=true,$count=200,$filter=false) {
		if ($username) {
			$twitter_url = 'http://api.twitter.com/1/statuses/user_timeline.json?screen_name=' . $username . '&exclude_replies=' . $exclude_replies . '&count=' . $count;
			$feed_data = $this->getCachedURL('com.twitter', 'user_' . $username . (string) $exclude_replies . $count, $twitter_url);

			// here we'll need to parse results and apply the filter
			
			return $feed_data;
		} else {
			return false;
		}
	}

	public function getSearchFeed($query) {
		if ($query) {
			$query = urlencode($query);
			$twitter_url = 'http://search.twitter.com/search.json?q=' . $query;
			return $this->getCachedURL('com.twitter', 'search_' . $query);
		} else {
			return false;
		}
	}

	public function prepMarkup($tweet) {
		$tmp_profile_img = $tweet->user->profile_image_url;
		if ($tmp_profile_img == 'http://static.twitter.com/images/default_profile_normal.png') {
			$tmp_profile_img = 'http://a2.twimg.com/sticky/default_profile_images/default_profile_' . rand(0, 6) . '_normal.png';
		}
		$innermarkup = "<div class=\"cashmusic_social cashmusic_twitter\"><img src=\"$tmp_profile_img\" class=\"cashmusic_twitter_avatar\" alt=\"avatar\" />"
		. "<div class=\"cashmusic_twitter_namespc\"><a href=\"http://twitter.com/" . $tweet->user->screen_name . "\">@" . $tweet->user->screen_name . "</a><br />" . $tweet->user->name . "</div><div class=\"cashmusic_clearall\">.</div>"
		. "<div class=\"tweet\">" . $tweet->text . '<div class="cashmusic_social_date"><a href="http://twitter.com/#!/' . $tweet->user->screen_name . '/status/' . $tweet->id_str . '" target="_blank">' . CASHSystem::formatAgo($tweet->created_at) . ' / twitter</a> </div></div>';
	}
} // END class 
?>