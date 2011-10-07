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

	public function getData() {
		$this->twitter_seed = new TwitterSeed();
		$all_feeds = array();
		$twitter_feeds = array();
		$tumblr_feeds = array();
		
		$feedcount = 1;
		foreach($this->options->twitter as $feedname => $feed) {
			$twitter_feeds['feed'.$feedcount] = $this->twitter_seed->getUserFeed($feed->twitterusername,$feed->twitterhidereplies,200,$feed->twitterfiltertype,$feed->twitterfiltervalue);
			$feedcount = $feedcount + 1;
		}
		$feedcount = 1;
		
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
			
			krsort($all_posts);
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