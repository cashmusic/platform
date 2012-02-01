<?php
/**
 * Email For Download element
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
class SocialFeeds extends ElementBase {
	const type = 'socialfeeds';
	const name = 'Social Feeds';
	const twitter_seed = false;
	const tumblr_seed = false;

	public function getData() {
		$this->twitter_seed = new TwitterSeed();
		$this->tumblr_seed = new TumblrSeed();
		$all_feeds = array();
		$twitter_feeds = array();
		$tumblr_feeds = array();
		
		$feedcount = 1;
		foreach($this->options->twitter as $feedname => $feed) {
			$twitter_request = $this->twitter_seed->getUserFeed($feed->twitterusername,$feed->twitterhidereplies,$this->options->post_limit,$feed->twitterfiltertype,$feed->twitterfiltervalue);
			if ($twitter_request) {
				$twitter_feeds['feed'.$feedcount] = $twitter_request;
				$feedcount = $feedcount + 1;
			}
		}
		foreach($this->options->tumblr as $feedname => $feed) {
			$tumblr_request = $this->tumblr_seed->getTumblrFeed($feed->tumblrurl,0,$feed->tumblrtag,(array) $feed->post_types);
			if ($tumblr_request) {
				$tumblr_feeds['feed'.$feedcount] = $tumblr_request;
				$feedcount = $feedcount + 1;
			}
		}
		
		$all_feeds['twitter'] = $twitter_feeds;
		$all_feeds['tumblr'] = $tumblr_feeds;
		
		return $all_feeds;
	}

	public function getMarkup() {
		$feed_data = $this->getData();
		$markup = '';
		if ($feed_data) {
			$all_posts = array();
			
			foreach ($feed_data['twitter'] as $feed) {
				foreach ($feed as $tweet) {
					$all_posts[strtotime($tweet->created_at)] = array(
						'type' => 'twitter',
						'markup' => $this->twitter_seed->prepMarkup($tweet)
					);
				}
			}

			foreach ($feed_data['tumblr'] as $feed) {
				foreach ($feed as $post) {
					$all_posts[$post->{'unix-timestamp'}] = array(
						'type' => 'tumblr',
						'markup' => $this->tumblr_seed->prepMarkup($post)
					);
				}
				
			}

			krsort($all_posts);
			$all_posts = array_slice($all_posts,0,$this->options->post_limit,true);
			foreach ($all_posts as $post) {
				$markup .= $post['markup'];
			}
		} else {
			// no dates matched
			$markup .= 'There are no posts to display right now.';
		}
		return $markup;	
	}
} // END class 
?>