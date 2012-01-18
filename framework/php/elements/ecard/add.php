<?php if (!isset($_POST['doelementadd'])) { ?>
	<form name="emailcollection" method="post" action="" id="emailcollection">
		<input type="hidden" name="doelementadd" value="makeitso" />
		<input type="hidden" name="element_type" value="ecard" />
		<h3>Element Details</h3>

		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" placeholder="Give It A Name" /> 
		
		<div class="row_seperator">.</div>
		<label for="message_instructions">Instructions Message</label><br />
		<input type="text" id="message_instructions" name="message_instructions" value="Enter your email and a few of your friends. You'll join our list and get a free download, your friends will be sent the ecard below. No email addresses will be shared." />

		<div class="row_seperator">.</div>
		<label for="image_url">Card Image URL</label><br />
		<input type="text" id="image_url" name="image_url" value="" />

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">

			<label for="asset_id">Target Mailing List</label><br />
			<select id="email_list_id" name="email_list_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('people_lists'); ?>
			</select>
			<br /><br />
			<input type='checkbox' class='checkorradio' name='do_not_verify' value='' /> <label for="do_not_verify">Skip email verification</label>

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
		<br />
		<label for="message_invalid_email">Invalid Email Error Message</label><br />
		<input type="text" id="message_invalid_email" name="message_invalid_email" value="Sorry, that email address wasn't valid. Please try again." />

		<div class="row_seperator">.</div>
		<label for="message_success">Success Message</label><br />
		<input type="text" id="message_success" name="message_success" value="Thanks! Your friends will see their cards any minute now, and you can grab your download here:" />

		<div class="row_seperator">.</div><br />
		<label for="email_subject">Friend Email Subject (Their email address will be appended)</label><br />
		<input type="text" id="email_subject" name="email_subject" value="An e-card from " />
		
		<div class="row_seperator">.</div>
		<label for="email_message">Friend Email Message (Plain text)</label><br />
		<textarea id="email_message" name="email_message"></textarea>
		
		<div class="row_seperator">.</div>
		<label for="email_html_message">Friend Email Message (HTML - blank will style plain text)</label><br />
		<textarea id="email_html_message" name="email_html_message"></textarea>

		<input type="hidden" name="comment_or_radio" value="none" />
		<input type="hidden" name="do_not_verify" value="1" />

		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Add That Element" />
		</div>

	</form>
		
<?php } else {
	
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	if (isset($_POST['do_not_verify'])) {
		$do_not_verify = 1;
	} else {
		$do_not_verify = 0;
	}
	
	$element_add_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'addelement',
			'name' => $_POST['element_name'],
			'type' => $_POST['element_type'],
			'options_data' => array(
				'message_invalid_email' => $_POST['message_invalid_email'],
				'message_instructions' => $_POST['message_instructions'],
				'image_url' => $_POST['image_url'],
				'email_subject' => $_POST['email_subject'],
				'email_message' => $_POST['email_message'],
				'email_html_message' => $_POST['email_html_message'],
				'message_success' => $_POST['message_success'],
				'email_list_id' => $_POST['email_list_id'],
				'asset_id' => $_POST['asset_id'],
				'do_not_verify' => $do_not_verify
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
