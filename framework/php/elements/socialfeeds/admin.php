<?php
 // Identify the workflow state:
if (AdminHelper::elementFormSubmitted($_POST)) {
	// parse for feeds
	$all_feeds = array();
	$tumblr_feeds = array();
	$twitter_feeds = array();
	foreach ($_POST as $key => $value) {
		if (substr($key,0,9) == 'tumblrurl' && $value !== '') {
			$tag = $_POST[str_replace('tumblrurl','tumblrtag',$key)];
			if (empty($tag)) {
				$tag = false;
			}
			$post_types = array(
				'regular' => false,
				'link' => false,
				'quote' => false,
				'photo' => false,
				'conversation' => false,
				'video' => false,
				'audio' => false,
				'answer' => false
			);
			if (isset($_POST[str_replace('tumblrurl','post_type_regular',$key)])) {
				$post_types['regular'] = true;
			}
			if (isset($_POST[str_replace('tumblrurl','post_type_link',$key)])) {
				$post_types['link'] = true;
			}
			if (isset($_POST[str_replace('tumblrurl','post_type_quote',$key)])) {
				$post_types['quote'] = true;
			}
			if (isset($_POST[str_replace('tumblrurl','post_type_photo',$key)])) {
				$post_types['photo'] = true;
			}
			if (isset($_POST[str_replace('tumblrurl','post_type_video',$key)])) {
				$post_types['video'] = true;
			}
			if (isset($_POST[str_replace('tumblrurl','post_type_audio',$key)])) {
				$post_types['audio'] = true;
			}
			if (isset($_POST[str_replace('tumblrurl','post_type_answer',$key)])) {
				$post_types['answer'] = true;
			}
			$tumblr_feeds[] = array(
				'tumblrurl' => $value,
				'tumblrtag' => $tag,
				'post_types' => $post_types
			);
		}
		if (substr($key,0,15) == 'twitterusername' && $value !== '') {
			$twitterusername = str_replace('@','',$value);
			if (isset($_POST[str_replace('twitterusername','twitterhidereplies',$key)])) {
				$twitterhidereplies = true;
			} else {
				$twitterhidereplies = false;
			}
			$twitterfiltertype = $_POST[str_replace('twitterusername','twitterfiltertype',$key)];
			$twitterfiltervalue = $_POST[str_replace('twitterusername','twitterfiltervalue',$key)];
			$twitter_feeds[] = array(
				'twitterusername' => $twitterusername,
				'twitterhidereplies' => $twitterhidereplies,
				'twitterfiltertype' => $twitterfiltertype,
				'twitterfiltervalue' => $twitterfiltervalue
			);
		}
	}
	$all_feeds['tumblr'] = $tumblr_feeds;
	$all_feeds['twitter'] = $twitter_feeds;
	$all_feeds['post_limit'] = $_POST['post_limit'];
	AdminHelper::handleElementFormPOST(
		$_POST,
		$cash_admin,
		$all_feeds
	);
}

$current_element = $cash_admin->getCurrentElement();
if ($current_element) {
	// Current element found, so fill in the 'edit' form, basics first:
	AdminHelper::setBasicElementFormData($cash_admin);
	// Now any element-specific options:
	$cash_admin->page_data['options_post_limit'] = $current_element['options']['post_limit'];
	// Deal with all the feed array mess:
	$cash_admin->page_data['tumblr_count'] = 1;
	if (is_array($current_element['options']['tumblr'])) {
		$tumblr_feeds = array();
		$feed_counter = 1;
		foreach ($current_element['options']['tumblr'] as $feed) {
			// Flattening each feed type for easier mustache checkbox shit-storming
			$formatted_feed = array(
				'tumblrurl' => $feed['tumblrurl'],
				'tumblrtag' => $feed['tumblrtag'],
				'feed_count' => $feed_counter
			);
			$tumblr_feeds[] = array_merge($formatted_feed,$feed['post_types']);
			$feed_counter++;
		}
		$cash_admin->page_data['tumblr_feeds'] = new ArrayIterator($tumblr_feeds);
		$cash_admin->page_data['tumblr_count'] = $feed_counter;
	} 
	$cash_admin->page_data['twitter_count'] = 1;
	if (is_array($current_element['options']['twitter'])) {
		// Add feed_count and a pre-built options string for the form
		$twitter_feeds = array();
		$feed_counter = 1;
		foreach ($current_element['options']['twitter'] as $feed) {
			switch ($feed['twitterfiltertype']) {
				case 'none':
					$feed['options_string'] = "<option value='none' selected='selected'>Do not filter</option><option value='contain'>Tweets containing:</option><option value='beginwith'>Tweets begin with:</option></select>";
					break;
				case 'contain':
					$feed['options_string'] = "<option value='none'>Do not filter</option><option value='contain' selected='selected'>Tweets containing:</option><option value='beginwith'>Tweets begin with:</option></select>";
					break;
				case 'beginwith':
					$feed['options_string'] = "<option value='none'>Do not filter</option><option value='contain'>Tweets containing:</option><option value='beginwith' selected='selected'>Tweets begin with:</option></select>";
					break;
			}
			$feed['feed_count'] = $feed_counter;
			$twitter_feeds[] = $feed;
			$feed_counter++;
		}
		$cash_admin->page_data['twitter_feeds'] = new ArrayIterator($twitter_feeds);
		$cash_admin->page_data['twitter_count'] = $feed_counter;
	}
}
?>