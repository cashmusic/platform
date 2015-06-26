<?php
/**
 * Tumblr library wrapper and public feed fetcher
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
 * This file is generously sponsored by sponsored by Bangsplat - blog.bangsplatpresents.com
 *
 **/
class TumblrSeed extends SeedBase {
	protected $twitter;

	public function __construct($user_id=false,$connection_id=false) {
		$this->settings_type = 'com.tumblr';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		$this->primeCache();
		if ($user_id && $connection_id) {
			$this->connectDB();
			if ($this->getCASHConnection()) {
				// fire up an instance of the lib
			} else {
				// error out — potentially to special error message page.
				// probably not doing any kind of authorized tumblr stuff at first.
			}
		}
	}

	public function getTumblrFeed($tumblr_domain,$start_at=0,$tagged=false,$post_types=false) {
		if ($tumblr_domain) {
			$default_post_types = array(
				'regular' => true,
				'link' => true,
				'quote' => false,
				'photo' => true,
				'conversation' => false,
				'video' => true,
				'audio' => true,
				'answer' => false
			);
			if (is_array($post_types)) {
				$final_post_types = array_merge($default_post_types, $post_types);
			} else {
				$final_post_types = $default_post_types;
			}
			$tumblr_domain = str_replace(array('http://','/'),'',$tumblr_domain);
			$tumblr_url = 'http://' . $tumblr_domain . '/api/read/json?start=' . $start_at . '&num=30';
			if ($tagged) {
				$tumblr_url .= '&tagged=' . urlencode($tagged);
			}
			$feed_data = $this->getCachedURL('com.tumblr', 'domain_' . str_replace('.','',$tumblr_domain) . $start_at, $tumblr_url, 'raw', false);

			if ($feed_data) {
				// tumblr's funny, JSONP only, so we cache its return and strip of some extra
				$feed_data = str_replace('var tumblr_api_read = ','',$feed_data); // strip off the variable declaration
				$feed_data = substr($feed_data,0,strlen($feed_data)-2); // and the trailing semicolon+newline

				// decode the trimmed content, then return just the posts
				$feed_data = json_decode($feed_data);
				$feed_data = $feed_data->posts;

				// make a dummy array to save final posts
				$final_feed_data = array();

				// loop through all the posts, filter by type
				foreach ($feed_data as $post) {
					if ($final_post_types[$post->type]) {
						$post->formatted_date = CASHSystem::formatTimeAgo($post->{'unix-timestamp'});
						$final_feed_data[] = $post;
					}
				}
				$feed_data = $final_feed_data;
			}

			return $feed_data;
		} else {
			return false;
		}
	}
} // END class
?>
