<?php 
$page_data = $page_request->response['payload'];
if (isset($_POST['doelementedit'])) {
	if (isset($_POST['do_not_verify'])) {
		$do_not_verify = 1;
	} else {
		$do_not_verify = 0;
	}
	$element_edit_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'editelement',
			'id' => $page_request->response['payload']['id'],
			'name' => $_POST['element_name'],
			'options_data' => array(
				'emal_list_id' => $_POST['emal_list_id'],
				'display_title' => $_POST['display_title'],
				'display_message' => $_POST['display_message']
			),
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
	<form name="signin" method="post" action="" id="signin">
		<input type="hidden" name="doelementedit" value="makeitso" />
		<input type="hidden" name="element_type" value="signin" />
		<h3>Element Details</h3>

		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" value="<?php echo $page_data['name']; ?>" /> 

		<div class="row_seperator">.</div>
			<label for="asset_id">Target Mailing List</label><br />
			<select id="emal_list_id" name="emal_list_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('user_lists',$page_data['options']->emal_list_id); ?>
			</select>

		<div class="row_seperator">.</div>
		<br />
		<label for="display_title">Display Title (blank for none)</label><br />
		<input type="text" id="display_title" name="display_title" value="<?php echo $page_data['options']->display_title; ?>" />

		<div class="row_seperator">.</div>
		<label for="display_message">Display Message</label><br />
		<input type="text" id="display_message" name="display_message" value="<?php echo $page_data['options']->display_message; ?>" />

		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Add That Element" />
		</div>

	</form>