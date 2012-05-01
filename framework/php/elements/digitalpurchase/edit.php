<?php 
$page_data = $cash_admin->getStoredResponse('originalelement',true);
if (isset($_POST['doelementedit'])) {
	$element_edit_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'editelement',
			'id' => $page_data['id'],
			'name' => $_POST['element_name'],
			'options_data' => array(
				'message_error' => $_POST['message_error'],
				'message_success' => $_POST['message_success'],
				'item_id' => $_POST['item_id'],
				'connection_id' => $_POST['connection_id']
			)
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
	<form method="post" action="">
		<input type="hidden" name="doelementedit" value="makeitso">
		<input type="hidden" name="element_id" value="<?php echo $page_data['id']; ?>">
		<h3>Element Details</h3>

		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" value="<?php echo $page_data['name']; ?>" /> 

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">
			<label for="item_id">Item For Sale</label><br />
			<select id="item_id" name="item_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('items',$page_data['options']['item_id']); ?>
			</select>
		</div>
		<div class="col_oneoftwo lastcol">

			<label for="connection_id">Connection to Use</label><br />
			<select id="connection_id" name="connection_id">
				<option value="0" selected="selected">None (Please add a commerce service)</option>
				<?php AdminHelper::echoConnectionsOptions('commerce',$page_data['options']['connection_id']) ?>
			</select>
		</div>

		<div class="row_seperator">.</div>
		<br />
		<label for="message_error">Error Message</label><br />
		<input type="text" id="message_error" name="message_error" value="<?php echo $page_data['options']['message_error']; ?>" />

		<div class="row_seperator">.</div>
		<label for="message_success">Success Message</label><br />
		<input type="text" id="message_success" name="message_success" value="<?php echo $page_data['options']['message_success']; ?>" />

		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Add That Element" />
		</div>

	</form>