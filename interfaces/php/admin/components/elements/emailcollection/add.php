<?php if (!isset($_POST['doelementadd'])) { ?>
	<form name="emailcollection" method="post" action="" id="emailcollection">
		<input type="hidden" name="doelementadd" value="makeitso" />
		<input type="hidden" name="element_type" value="emailcollection" />
		<h3>Element Details</h3>

		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" placeholder="Give It A Name" /> 

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">

			<label for="asset_id">Target Mailing List</label><br />
			<select id="emal_list_id" name="emal_list_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('user_lists'); ?>
			</select>

		</div>
		<div class="col_oneoftwo lastcol">

			<label for="asset_id">The Downloadable Asset</label><br />
			<select id="asset_id" name="asset_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('assets'); ?>
			</select>
			
			<br /><br />
	
			<a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/assets/add/single/"><small>OR ADD NEW ASSET</small></a>
			
		</div>

		<div class="row_seperator">.</div>
		<label for="message_invalid_email">Invalid Email Error Message</label><br />
		<input type="text" id="message_invalid_email" name="message_invalid_email" value="Sorry, that email address wasn't valid. Please try again." />

		<div class="row_seperator">.</div>
		<label for="message_privacy">Privacy Message</label><br />
		<input type="text" id="message_privacy" name="message_privacy" value="We won't share, sell, or be jerks with your email address." />

		<div class="row_seperator">.</div>
		<label for="message_success">Success Message</label><br />
		<input type="text" id="message_success" name="message_success" value="Thanks! You're all signed up. Here's your download:" />

		<!--
		<div class="row_seperator">.</div><br />

		<label for="comment_or_radio">Comment Or Agreement</label><br />
		<input type="radio" name="comment_or_radio" class="checkorradio" value="none" checked="checked" /> Neither &nbsp; &nbsp; <input type="radio" name="comment_or_radio" class="checkorradio" value="comment" /> Comment &nbsp; &nbsp; <input type="radio" name="comment_or_radio" class="checkorradio" value="agreement" /> Agreement 
		-->
		<input type="hidden" name="comment_or_radio" value="none" />

		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Add That Element" />
		</div>

	</form>
		
<?php } else {
	
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	
	$element_add_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'addelement',
			'name' => $_POST['element_name'],
			'type' => $_POST['element_type'],
			'options_data' => array(
				'message_invalid_email' => $_POST['message_invalid_email'],
				'message_privacy' => $_POST['message_privacy'],
				'message_success' => $_POST['message_success'],
				'emal_list_id' => $_POST['emal_list_id'],
				'asset_id' => $_POST['asset_id'],
				'comment_or_radio' => $_POST['comment_or_radio']
			),
			'user_id' => $effective_user
		)
	);
	if ($element_add_request->response['status_uid'] == 'element_addelement_200') {
	?>
	
		<h3>Success</h3>
		<p>
		Your new <b>Email Collection</b> element is ready to go. To begin using it immediately insert
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
