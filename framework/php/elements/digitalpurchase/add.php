<?php if (!isset($_POST['doelementadd'])) { ?>
	<form name="emailcollection" method="post" action="" id="emailcollection">
		<input type="hidden" name="doelementadd" value="makeitso" />
		<input type="hidden" name="element_type" value="digitalpurchase" />
		<h3>Element Details</h3>

		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" placeholder="Give It A Name" /> 

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">
			<label for="item_id">Item For Sale</label><br />
			<select id="item_id" name="item_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('items'); ?>
			</select>
		</div>
		<div class="col_oneoftwo lastcol">

			<label for="connection_id">Connection to Use</label><br />
			<select id="connection_id" name="connection_id">
				<option value="0" selected="selected">None (Please add a commerce service)</option>
				<?php AdminHelper::echoConnectionsOptions('commerce') ?>
			</select>
		</div>

		<div class="row_seperator">.</div>
		<br />
		<label for="message_error">Error Message</label><br />
		<input type="text" id="message_error" name="message_error" value="There was an error processing your payment. Please reload and try again." />

		<div class="row_seperator">.</div>
		<label for="message_success">Success Message</label><br />
		<input type="text" id="message_success" name="message_success" value="Thank you! Here's your download. Mobile users check your inbox and spam folders for a download link." />

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
				'message_error' => $_POST['message_error'],
				'message_success' => $_POST['message_success'],
				'item_id' => $_POST['item_id'],
				'connection_id' => $_POST['connection_id']
			),
			'user_id' => $effective_user
		)
	);
	if ($element_add_request->response['status_uid'] == 'element_addelement_200') {
	?>
	
		<h3>Success</h3>
		<p>
		Your new <b>Digital Purchase</b> element is ready to go. To begin using it immediately insert
		this embed code on any page:
		</p>
		<code>
			&lt;?php CASHSystem::embedElement(<?php echo $element_add_request->response['payload']; ?>); // CASH element (<?php echo $_POST['element_name'] . ' / ' . $_POST['element_type']; ?>) ?&gt;
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
