<?php if (!isset($_POST['doelementadd'])) { ?>
	<form method="post" action="">
		<input type="hidden" name="doelassetadd" value="makeitso" />
		<input type="hidden" name="asset_type" value="single" />
		<div class="col_onehalf">
			<h3>Asset Details</h3>
			
			<label for="settings_id">Use Settings</label><br />
			<select id="settings_id" name="settings_id">
				<option value="0">None (Normal http://... link)</option>
			</select>
			
			<div class="row_seperator">.</div>
	
			<label for="Asset Title">Title / Name</label><br />
			<input type="text" id="asset_title" name="asset_title" value="" placeholder="Give It A Name" />
	
			<label for="asset_location">Location (URI)</label><br />
			<input type="text" id="asset_location" name="asset_location" value="" placeholder="URL, S3 path, or SoundCloud URL" />
			
		</div>

		<div class="col_onehalf lastcol">
			<h3>&nbsp;</h3>
			<label for="asset_description">Description</label><br />
			<textarea rows="3" id="asset_description" name="asset_description"></textarea>
			<div class="row_seperator">.</div>
			<div>
				<label>Tags</label><br />
				<a href="#" class="injectbefore" rev="<input type='text' name='tag' value='' placeholder='Tag' />"><small>+ ADD TAG</small></a>
			</div>
			<div class="row_seperator">.</div>
			<div>
				<label>Metadata</label><br />
				<a href="#" class="injectbefore" rev="<div class='col_onehalf'><input type='text' name='metadatakey' value='' placeholder='Key (Data Type)' /></div><div class='col_onehalf lastcol'><input type='text' name='metadatavalue' value='' placeholder='Value' /></div>"><small>+ ADD CUSTOM METADATA</small></a>
			</div>
		</div>
		<div class="row_seperator">.</div><br />
		<div class="tar">
			<input class="button" type="submit" value="Add That Element" />
		</div>

	</form>
		
<?php } else {
	
	$effective_user = getPersistentData('cash_effective_user');
	
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
		Your new <b>Email For Download</b> element is ready to go. To begin using it immediately insert
		this embed code on any page:
		</p>
		<code>
			&lt;?php cash_embedElement(<?php echo $element_add_request->response['payload']['element_id']; ?>); // CASH element (<?php echo $_POST['element_name'] . ' / ' . $_POST['element_type']; ?>) ?&gt;
		</code>
		<br />
		<p>
		Enjoy!
		</p>

	<?php } else { ?>
		
		<h3>Error</h3>
		<p>
		There was a problem creating the element. <a href="./">Please try again.</a>
		</p>

<?php 
	}
}	
?>