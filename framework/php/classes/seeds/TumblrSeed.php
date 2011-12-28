<?php
/**
 * Tumblr library wrapper and public feed fetcher
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
	
	public function getTumblrFeed($tumblr_domain,$start_at=0) {
		if ($tumblr_domain) {
			$tumblr_url = 'http://' . $tumblr_domain . '/api/read/json?start=' . $start_at;
			$feed_data = $this->getCachedURL('com.tumblr', 'domain_' . $tumblr_domain . $start_at, $tumblr_url, 'raw', false);

			// tumblr's funny, so we cache its return and strip of some extra
			$feed_data = str_replace('var tumblr_api_read = ','',$feed_data); // strip off the variable declaration
			$feed_data = substr($feed_data,0,strlen($feed_data)-2); // and the trailing semicolon+newline

			// decode the trimmed content, then return just the posts
			$feed_data = json_decode($feed_data);
			$feed_data = $feed_data->posts;

			return $feed_data;
		} else {
			return false;
		}
	}

	public function prepMarkup($post) {
		if ($post->type == 'regular') {
			$textbody = '';
			$textbodyarray = explode('.',strip_tags($post->{'regular-body'}));
			if (count($textbodyarray) > 3) {
				$textbodyarray = array_slice($textbodyarray,0,3);
			}
			$textbody = implode('.',$textbodyarray) . '...';

			$innermarkup = "<div class=\"cashmusic_social cashmusic_tumblr\">"
			. '<h2><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . $post->{$post->type . '-title'} . '</a></h2><div>' . $textbody . '</div><div class="cashmusic_social_date"><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . CASHSystem::formatAgo($post->{'unix-timestamp'}) . ' / tumblr</a> </div>'
			. '<div style="clear:both;overflow:hidden;visibility:hidden;height:1px;">.</div></div>';
		} else if ($post->type == 'photo') {
			$innermarkup = "<div class=\"cashmusic_social cashmusic_tumblr\">"
			. '<div><img src="' . $post->{'photo-url-500'} . '" width="100%" alt="" /><br />' . $post->{'photo-caption'} . '</div><div class="cashmusic_social_date"><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . CASHSystem::formatAgo($post->{'unix-timestamp'}) . ' / tumblr</a> </div>'
			. '<div style="clear:both;overflow:hidden;visibility:hidden;height:1px;">.</div></div>';
		} else if ($post->type == 'video') {
			$innermarkup = "<div class=\"cashmusic_social cashmusic_tumblr\">"
			. '<div><div class="cashmusic_social_video_container">' . $post->{'video-player'} . '</div><br />' . $post->{'video-caption'} . '</div><div class="cashmusic_social_date"><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . CASHSystem::formatAgo($post->{'unix-timestamp'}) . ' / tumblr</a> </div>'
			. '<div style="clear:both;overflow:hidden;visibility:hidden;height:1px;">.</div></div>';
		}
		/*
		The CSS to go along with the video container:
		
		Thanks to http://www.alistapart.com/articles/creating-intrinsic-ratios-for-video/
		
		echo '<style type="text/css">';
		echo '.cashmusic_video_container {position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden;}';
		echo '.cashmusic_video_container iframe, .cashmusic_video_container object, .cashmusic_video_container embed {position:absolute;top:0;left:0;width:100%;height:100%;}';
		echo '</style>';
		*/
	}
} // END class 
?>