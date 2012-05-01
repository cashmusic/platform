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
				'storedcotent' => $_POST['element_content'],
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
<form name="staticcontent" method="post" action="" id="socialfeeds">
	<input type="hidden" name="doelementedit" value="makeitso" />
	<input type="hidden" name="element_type" value="staticcontent" />
	<h3>Element Details</h3>
	
	<label for="element_name">Name</label><br />
	<input type="text" id="element_name" name="element_name" value="<?php echo $page_data['name']; ?>" /> 

	<div class="row_seperator">.</div><br />
	<div>
		<label>Content</label><br />
		<textarea id="element_content" name="element_content" class="tall"><?php echo $page_data['options']['storedcotent']; ?></textarea>
	</div>
	<div>
		<br />
		<input class="button" type="submit" value="Add That Element" />
	</div>

</form>
