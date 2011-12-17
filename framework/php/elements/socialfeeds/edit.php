<?php 
$page_data = $page_request->response['payload'];
if (isset($_POST['doelementedit'])) {
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
	
	$element_edit_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'editelement',
			'id' => $page_request->response['payload']['id'],
			'name' => $_POST['element_name'],
			'options_data' => $all_feeds
		)
	);
	if ($element_edit_request->response['status_uid'] == 'element_editelement_200') {
		$page_data = $element_edit_request->response['payload'];
	?>
	
		<h3>Success</h3>
		<p>
		</p>
		Your edits have been made and can be seen below. To embed the element us this code:
		<code>
			&lt;?php CASHSystem::embedElement(<?php echo $page_data['id']; ?>); // CASH element (<?php echo $page_data['name'] . ' / ' . $page_data['type']; ?>) ?&gt;
		</code>
		<br />

	<?php } else { ?>
		
		<h3>Error</h3>
		<p>
		There was a problem editing the element. <a href="./">Please try again.</a>
		</p>

<?php
	}
}
?>
	<form name="socialfeeds" method="post" action="">
		<input type="hidden" name="doelementedit" value="makeitso" />
		<input type="hidden" name="element_id" value="<?php echo $page_data['id']; ?>">
		<h3>Element Details</h3>
	
		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" value="<?php echo $page_data['name']; ?>" /> 

		<div class="row_seperator">.</div><br />
		<div>
			<label>Twitter</label><br />
			<?php
				$twitter_counter = 1;
				foreach ($page_data['options']->twitter as $tw) {
					if ($tw->twitterhidereplies) {
						$checkstring = "checked='checked'";
					} else {
						$checkstring = "";
					}
					switch ($tw->twitterfiltertype) {
						case "none":
							$optionsstring = "<option value='none' selected='selected'>Do not filter</option><option value='contain'>Tweets containing:</option><option value='beginwith'>Tweets begin with:</option></select>";
							break;
						case "contain":
 							$optionsstring = "<option value='none'>Do not filter</option><option value='contain' selected='selected'>Tweets containing:</option><option value='beginwith'>Tweets begin with:</option></select>";
							break;
						case "beginwith":
							$optionsstring = "<option value='none'>Do not filter</option><option value='contain'>Tweets containing:</option><option value='beginwith' selected='selected'>Tweets begin with:</option></select>";
							break;
					}
					
					echo "<div class='col_oneofthree'><input type='text' name='twitterusername$twitter_counter' value='{$tw->twitterusername}' /><br /><input type='checkbox' class='checkorradio' name='twitterhidereplies$twitter_counter' value=''$checkstring /> Hide @-replies?</div><div class='col_oneofthree'><select name='twitterfiltertype$twitter_counter'>$optionsstring</select></div><div class='col_oneofthree lastcol'><input type='text' name='twitterfiltervalue$twitter_counter' value='{$tw->twitterfiltervalue}' /></div><div class='row_seperator'>.</div><br />";
					$twitter_counter = $twitter_counter+1;
				}
			?>
			<a href="#" class="injectbefore" rel="<?php echo $twitter_counter; ?>" rev="<div class='col_oneoftwo'><input type='text' name='twitterusername' value='' placeholder='@username' /><br /><input type='checkbox' class='checkorradio' name='twitterhidereplies' value='' checked='checked' /> Hide @-replies?</div><div class='col_oneoftwo lastcol'><div class='col_oneoftwo'><select name='twitterfiltertype'><option value='none' selected='selected'>Do not filter</option><option value='contain'>Tweets containing:</option><option value='beginwith'>Tweets begin with:</option></select></div><div class='col_oneoftwo lastcol'><input type='text' name='twitterfiltervalue' value='' placeholder='Filter value' /></div></div><div class='row_seperator'>.</div><br />"><small>+ ADD TWITTER FEED</small></a>
		</div>
		<div class="row_seperator">.</div><br />
		<div>
			<label>Tumblr</label><br />
			<?php
				$tumblr_counter = 1;
				foreach ($page_data['options']->tumblr as $tu) {
					echo "<input type='text' name='tumblrurl$tumblr_counter' value='$tu' />";
					$tumblr_counter = $tumblr_counter+1;
				}
			?>
			<a href="#" class="injectbefore" rev="<input type='text' name='tumblrurl' value='' placeholder='Tumblr URL' />"><small>+ ADD TUMBLR FEED</small></a>
		</div>
		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Add That Element" />
		</div>

	</form>