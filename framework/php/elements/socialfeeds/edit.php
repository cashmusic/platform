<?php 
$page_data = $cash_admin->getStoredResponse('originalelement',true);
if (isset($_POST['doelementedit'])) {
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
	
	$element_edit_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'editelement',
			'id' => $page_data['id'],
			'name' => $_POST['element_name'],
			'options_data' => $all_feeds
		)
	);
	if ($element_edit_request->response['status_uid'] == 'element_editelement_200') {
		$element_edit_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getelement',
				'id' => $page_data['id']
			)
		);
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

		<div class="col_twoofthree">
			<label for="element_name">Name</label><br />	
			<input type="text" id="element_name" name="element_name" value="<?php echo $page_data['name']; ?>" /> 
		</div>
		<div class="col_oneofthree lastcol">
			<label for="post_limit">Max posts returned</label><br />	
			<input type="text" id="post_limit" name="post_limit" value="<?php echo $page_data['options']->{'post_limit'} ?>" /> 
		</div>

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
					
					echo "<div class='col_oneofthree'><label>Username</label><input type='text' name='twitterusername$twitter_counter' value='{$tw->twitterusername}' /><br /><input type='checkbox' class='checkorradio' name='twitterhidereplies$twitter_counter' value=''$checkstring /> Hide @-replies?</div><div class='col_oneofthree'><label>Fliter?</label><select name='twitterfiltertype$twitter_counter'>$optionsstring</select></div><div class='col_oneofthree lastcol'><label>Fliter By</label><input type='text' name='twitterfiltervalue$twitter_counter' value='{$tw->twitterfiltervalue}' /></div><div class='row_seperator'>.</div><br />";
					$twitter_counter = $twitter_counter+1;
				}
			?>
			<a href="#" class="injectbefore" rel="<?php echo $twitter_counter; ?>" rev="<div class='col_oneoftwo'><label>Username</label><input type='text' name='twitterusername' value='' placeholder='@username' /><br /><input type='checkbox' class='checkorradio' name='twitterhidereplies' value='' checked='checked' /> Hide @-replies?</div><div class='col_oneoftwo lastcol'><div class='col_oneoftwo'><label>Filter?</label><select name='twitterfiltertype'><option value='none' selected='selected'>Do not filter</option><option value='contain'>Tweets containing:</option><option value='beginwith'>Tweets begin with:</option></select></div><div class='col_oneoftwo lastcol'><label>Filter By</label><input type='text' name='twitterfiltervalue' value='' placeholder='Filter value' /></div></div><div class='row_seperator'>.</div><br />"><small>+ ADD TWITTER FEED</small></a>
		</div>
		<div class="row_seperator">.</div><br />
		<div>
			<label>Tumblr</label><br />
			<?php
				$tumblr_counter = 1;
				foreach ($page_data['options']->tumblr as $tu) {
					$checkstring = '';
					foreach (array('regular','photo','video','link','audio','quote','answer') as $post_type) {
						$checkstring .= "<input type='checkbox' class='checkorradio' name='post_type_$post_type$tumblr_counter' ";
						if ($tu->post_types->{$post_type}) {
							$checkstring .= "checked='checked'";
						}
						$checkstring .= " /> $post_type &nbsp;";
					}
					echo "<div class='col_twoofthree'><label>Tumblr URL</label><input type='text' name='tumblrurl$tumblr_counter' value='{$tu->tumblrurl}' /></div><div class='col_oneofthree lastcol'><label>Filter by tag</label><input type='text' name='tumblrtag$tumblr_counter' value='{$tu->tumblrtag}' placeholder='do not filter' /></div><br /><div><br /><br /><label>Post types</label><br />$checkstring</div><br /><br />";
					$tumblr_counter = $tumblr_counter+1;
				}
			?>
			<a href="#" class="injectbefore" rev="<input type='text' name='tumblrurl' value='' placeholder='Tumblr URL' />"><small>+ ADD TUMBLR FEED</small></a>
		</div>
		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Edit That Element" />
		</div>

	</form>