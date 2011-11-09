<?php 
$page_data = $page_request->response['payload'];
if (isset($_POST['doelementedit'])) {
	$element_edit_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'editelement',
			'id' => $page_request->response['payload']['id'],
			'name' => $_POST['element_name'],
			'options_data' => array(
				'message_invalid_email' => $_POST['message_invalid_email'],
				'message_privacy' => $_POST['message_privacy'],
				'message_success' => $_POST['message_success'],
				'emal_list_id' => $_POST['emal_list_id'],
				'asset_id' => $_POST['asset_id'],
				'comment_or_radio' => $_POST['comment_or_radio']
			)
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
	<form method="post" action="">
		<input type="hidden" name="doelementedit" value="makeitso">
		<input type="hidden" name="element_id" value="<?php echo $page_data['id']; ?>">
		<h3>Element Details</h3>

		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" value="<?php echo $page_data['name']; ?>" /> 

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">

			<label for="asset_id">Target Mailing List</label><br />
			<select id="emal_list_id" name="emal_list_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('user_lists',$page_data['options']->emal_list_id); ?>
			</select>

		</div>
		<div class="col_oneoftwo lastcol">

			<label for="asset_id">The Downloadable Asset</label><br />
			<select id="asset_id" name="asset_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('assets',$page_data['options']->asset_id); ?>
			</select>
			
			<br /><br />
	
			<a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/assets/add/"><small>OR ADD NEW ASSET</small></a>
			
		</div>

		<div class="row_seperator">.</div>
		<label for="message_invalid_email">Invalid Email Error Message</label><br />
		<input type="text" id="message_invalid_email" name="message_invalid_email" value="<?php echo $page_data['options']->message_invalid_email; ?>" />

		<div class="row_seperator">.</div>
		<label for="message_privacy">Privacy Message</label><br />
		<input type="text" id="message_privacy" name="message_privacy" value="<?php echo $page_data['options']->message_privacy; ?>" />

		<div class="row_seperator">.</div>
		<label for="message_success">Success Message</label><br />
		<input type="text" id="message_success" name="message_success" value="<?php echo $page_data['options']->message_success; ?>" />

		<!--
		<div class="row_seperator">.</div><br />

		<label for="comment_or_radio">Comment Or Agreement</label><br />
		<input type="radio" name="comment_or_radio" class="checkorradio" value="none" checked="checked" /> Neither &nbsp; &nbsp; <input type="radio" name="comment_or_radio" class="checkorradio" value="comment" /> Comment &nbsp; &nbsp; <input type="radio" name="comment_or_radio" class="checkorradio" value="agreement" /> Agreement 
		
		-->

		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Edit The Element" />
		</div>

	</form>