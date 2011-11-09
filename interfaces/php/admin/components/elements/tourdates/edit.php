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
				'visible_event_types' => $_POST['visible_event_types']
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
		<div class="col_oneoftwo">
			<h3>Element Details</h3>
		
			<label for="element_name">Name</label><br />
			<input type="text" id="element_name" name="element_name" value="<?php echo $page_data['name']; ?>" /> 
		</div>

		<div class="col_oneoftwo lastcol">
			<h3>&nbsp;</h3>
			<label for="visible_event_types">Which Dates Should It Show?</label><br />
			<input type="radio" name="visible_event_types" class="checkorradio" value="upcoming" <?php if ($page_data['options']->visible_event_types == 'upcoming') { echo 'checked="checked"'; } ?> /> Upcoming Dates <br />
			<input type="radio" name="visible_event_types" class="checkorradio" value="archive" <?php if ($page_data['options']->visible_event_types == 'archive') { echo 'checked="checked"'; } ?> /> Archive Dates <br /> 
			<input type="radio" name="visible_event_types" class="checkorradio" value="both" <?php if ($page_data['options']->visible_event_types == 'both') { echo 'checked="checked"'; } ?> /> All Dates 
		</div>
		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Edit The Element" />
		</div>

	</form>