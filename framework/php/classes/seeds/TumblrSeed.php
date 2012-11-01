<?php
/**
 * Tumblr library wrapper and public feed fetcher
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
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
			$tumblr_url = 'http://' . $tumblr_domain . '/api/read/json?start=' . $start_at . 'num=30';
			if ($tagged) {
				$tumblr_url .= '&tagged=' . urlencode($tagged);
			}
			$feed_data = $this->getCachedURL('com.tumblr', 'domain_' . $tumblr_domain . $start_at, $tumblr_url, 'raw', false);

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

	public function prepMarkup($post,$summarize=false) {
		$innermarkup = false;
		switch ($post->type) {
			case 'regular':
				if ($summarize) {
					$textbody = '';
					$textbodyarray = explode('.',strip_tags($post->{'regular-body'}));
					if (count($textbodyarray) > 3) {
						$textbodyarray = array_slice($textbodyarray,0,3);
					}
					$textbody = implode('.',$textbodyarray) . '...';
				} else {
					$textbody = $post->{'regular-body'};
				}

				$innermarkup = "<div class=\"cashmusic_social cashmusic_tumblr\">"
				. '<h2><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . $post->{$post->type . '-title'} . '</a></h2><div>' . $textbody . '</div><div class="cashmusic_social_date"><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . CASHSystem::formatTimeAgo($post->{'unix-timestamp'}) . ' / tumblr</a> </div>'
				. '<div class="cashmusic_clearall">.</div></div>';
				break;
			case 'photo':
				$innermarkup = "<div class=\"cashmusic_social cashmusic_tumblr\">"
				. '<div class="cashmusic_social_photo"><img src="' . $post->{'photo-url-500'} . '" width="100%" alt="" /><br />' . $post->{'photo-caption'} . '</div><div class="cashmusic_social_date"><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . CASHSystem::formatTimeAgo($post->{'unix-timestamp'}) . ' / tumblr</a> </div>'
				. '<div class="cashmusic_clearall">.</div></div>';
				break;
			case 'video':
				$innermarkup = "<div class=\"cashmusic_social cashmusic_tumblr\">"
				. '<div class="cashmusic_social_video"><div class="cashmusic_social_video_container">' . $post->{'video-player'} . '</div><br />' . $post->{'video-caption'} . '</div><div class="cashmusic_social_date"><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . CASHSystem::formatTimeAgo($post->{'unix-timestamp'}) . ' / tumblr</a> </div>'
				. '<div class="cashmusic_clearall">.</div></div>';
				break;
			case 'audio':
				$innermarkup = "<div class=\"cashmusic_social cashmusic_tumblr\">"
				. '<div class="cashmusic_social_audio"><a><div class="cashmusic_social_audio_container">' . $post->{'audio-player'} . '</div><br />' . $post->{'audio-caption'} . '</div><div class="cashmusic_social_date"><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . CASHSystem::formatTimeAgo($post->{'unix-timestamp'}) . ' / tumblr</a> </div>'
				. '<div class="cashmusic_clearall">.</div></div>';
				break;
			case 'link':
				$description = '';
				if ($summarize && !empty($post->{'link-description'})) {
					$descriptionarray = explode('.',strip_tags($post->{'link-description'}));
					if (count($descriptionarray) > 3) {
						$descriptionarray = array_slice($descriptionarray,0,3);
					}
					$description = implode('.',$descriptionarray) . '...';
				} else {
					$description = $post->{'link-description'};
				}
				$innermarkup = "<div class=\"cashmusic_social cashmusic_tumblr\">"
				. '<div class="cashmusic_social_link"><a href="' . $post->{'link-url'} . '">' . $post->{'link-text'} . '</a><div>' . $description . '</div></div><div class="cashmusic_social_date"><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . CASHSystem::formatTimeAgo($post->{'unix-timestamp'}) . ' / tumblr</a> </div>'
				. '<div class="cashmusic_clearall">.</div></div>';
				break;
			case 'answer':
				$innermarkup = "<div class=\"cashmusic_social cashmusic_tumblr\">"
				. '<div class="cashmusic_social_answer"><span class="cashmusic_social_answer_q">' . $post->question . '</span><span class="cashmusic_social_answer_a">' . $post->answer . '</span></div><div class="cashmusic_social_date"><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . CASHSystem::formatTimeAgo($post->{'unix-timestamp'}) . ' / tumblr</a> </div>'
				. '<div class="cashmusic_clearall">.</div></div>';
				break;
			case 'quote':
				$innermarkup = "<div class=\"cashmusic_social cashmusic_tumblr\">"
				. '<div class="cashmusic_social_quote"><span class="cashmusic_social_quote">' . $post->{'quote-text'} . '</span><span class="cashmusic_social_quote_src">' . $post->{'quote-source'} . '</span></div><div class="cashmusic_social_date"><a href="' . $post->{'url-with-slug'} . '" target="_blank">' . CASHSystem::formatTimeAgo($post->{'unix-timestamp'}) . ' / tumblr</a> </div>'
				. '<div class="cashmusic_clearall">.</div></div>';
				break;
		}
		
		return $innermarkup;
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