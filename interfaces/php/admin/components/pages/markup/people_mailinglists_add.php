<?php if (!isset($_POST['dolistadd'])) { ?>
	<form method="post" action="">
		<input type="hidden" name="dolistadd" value="makeitso" />
		<div class="col_onehalf">
			<h3>List Details</h3>
			
			<label for="list_name">Name</label><br />
			<input type="text" id="list_name" name="list_name" value="" placeholder="Give It A Name" />

			<div class="row_seperator">.</div>

			<label for="settings_id">Connect To</label><br />
			<select id="settings_id" name="settings_id">
				<option value="0" selected="selected">None (local list only)</option>
				<?php echoSettingsOptions('lists') ?>
			</select>
		</div>

		<div class="col_onehalf lastcol">
			<h3>&nbsp;</h3>
			<label for="list_description">Description</label><br />
			<textarea rows="3" id="list_description" name="list_description"></textarea>
		</div>

		<div class="row_seperator">.</div><br />
		<div class="tar">
			<input class="button" type="submit" value="Add The New List" />
		</div>

	</form>
		
<?php } else {
	if ($list_add_request->response['status_uid'] == 'people_addlist_200') {
	
	?>

		<h3>Success</h3>
		<p>
		The new list is good and ready to use.
		</p>
		<a href="./"><b>Add another?</b></a>
		<br />

	<?php } else { ?>
		
		<h3>Error</h3>
		<p>
		There was a problem adding the list. <a href="./">Please try again.</a>
		</p>

<?php 
	}
}	
?>