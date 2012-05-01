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
				'email_list_id' => $_POST['email_list_id'],
				'display_title' => $_POST['display_title'],
				'display_message' => $_POST['display_message']
			)
		)
	);
	if ($element_edit_request->response['payload']) {
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
	<form name="signin" method="post" action="" id="signin">
		<input type="hidden" name="doelementedit" value="makeitso" />
		<input type="hidden" name="element_type" value="signin" />
		<h3>Element Details</h3>

		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" value="<?php echo $page_data['name']; ?>" /> 

		<div class="row_seperator">.</div>
			<label for="email_list_id">Target Mailing List</label><br />
			<select id="email_list_id" name="email_list_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('people_lists',$page_data['options']['email_list_id']); ?>
			</select>

		<div class="row_seperator">.</div>
		<br />
		<label for="display_title">Display Title (blank for none)</label><br />
		<input type="text" id="display_title" name="display_title" value="<?php echo $page_data['options']['display_title']; ?>" />

		<div class="row_seperator">.</div>
		<label for="display_message">Display Message</label><br />
		<input type="text" id="display_message" name="display_message" value="<?php echo $page_data['options']['display_message']; ?>" />

		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Add That Element" />
		</div>

	</form>