<?php if (!isset($_POST['doelementadd'])) { ?>
	<form name="socialfeeds" method="post" action="" id="socialfeeds">
		<input type="hidden" name="doelementadd" value="makeitso" />
		<input type="hidden" name="element_type" value="socialfeeds" />
		<h3>Element Details</h3>
		
		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" placeholder="Give It A Name" /> 

		<div class="row_seperator">.</div><br />
		<div>
			<label>Twitter</label><br />
			<a href="#" class="injectbefore" rev="<div class='col_oneofthree'><input type='text' name='twitterusername' value='' placeholder='@username' /><br /><input type='checkbox' class='checkorradio' name='twitterhidereplies' value='' checked='checked' /> Hide @-replies?</div><div class='col_oneofthree'><select name='twitterfiltertype'><option value='none' selected='selected'>Do not filter</option><option value='contain'>Tweets containing:</option><option value='beginwith'>Tweets begin with:</option></select></div><div class='col_oneofthree lastcol'><input type='text' name='twitterfiltervalue' value='' placeholder='Filter value' /></div><div class='row_seperator'>.</div><br />"><small>+ ADD TWITTER FEED</small></a>
		</div>
		<div class="row_seperator">.</div><br />
		<div>
			<label>Tumblr</label><br />
			<a href="#" class="injectbefore" rev="<input type='text' name='tumblrurl' value='' placeholder='Tumblr URL' />"><small>+ ADD TUMBLR FEED</small></a>
		</div>
		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Add That Element" />
		</div>

	</form>
		
<?php } else {
	
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');

	// parse for feeds
	$all_feeds = array();
	$tumblr_feeds = array();
	$twitter_feeds = array();
	foreach ($_POST as $key => $value) {
		if (substr($key,0,9) == 'tumblrurl' && $value !== '') {
			$tumblr_feeds[] = $value;
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

	$element_add_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'addelement',
			'name' => $_POST['element_name'],
			'type' => $_POST['element_type'],
			'options_data' => $all_feeds,
			'user_id' => $effective_user
		)
	);
	if ($element_add_request->response['status_uid'] == 'element_addelement_200') {
	?>
	
		<h3>Success</h3>
		<p>
		Your new <b>Social Feeds</b> element is ready to go. To begin using it immediately insert
		this embed code on any page:
		</p>
		<code>
			&lt;?php CASHSystem::embedElement(<?php echo $element_add_request->response['payload']['element_id']; ?>); // CASH element (<?php echo $_POST['element_name'] . ' / ' . $_POST['element_type']; ?>) ?&gt;
		</code>
		<br />
		<p>
		Enjoy!
		</p>

	<?php } else { ?>
		
		<h3>Error</h3>
		<p>
		There was a problem creating the element. <a href="./">Please try again.</a>
<!-- <? var_dump($element_add_request->response) ?> -->
		</p>

<?php 
	}
}	
?>
