<?php if (!isset($_POST['doelementadd'])) { ?>
	<form name="tourdates" method="post" action="">
		<input type="hidden" name="doelementadd" value="makeitso" />
		<input type="hidden" name="element_type" value="tourdates" />
		<div class="col_oneoftwo">
			<h3>Element Details</h3>
		
			<label for="element_name">Name</label><br />
			<input type="text" id="element_name" name="element_name" placeholder="Give It A Name" /> 
		</div>

		<div class="col_oneoftwo lastcol">
			<h3>&nbsp;</h3>
			<label for="visible_event_types">Which Dates Should It Show?</label><br />
			<input type="radio" name="visible_event_types" class="checkorradio" value="upcoming" checked="checked" /> Upcoming Dates <br />
			<input type="radio" name="visible_event_types" class="checkorradio" value="archive" /> Archive Dates <br /> 
			<input type="radio" name="visible_event_types" class="checkorradio" value="both" /> All Dates 
		</div>
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
				'visible_event_types' => $_POST['visible_event_types']
			),
			'user_id' => $effective_user
		)
	);
	if ($element_add_request->response['status_uid'] == 'element_addelement_200') {
	?>
	
		<h3>Success</h3>
		<p>
		Your new <b>Tour Dates</b> element is ready to go. To begin using it immediately insert
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
