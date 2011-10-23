<?php 
	$current_list = $cash_admin->getStoredResponse('getlistinfo', true);
	if (isset($_POST['dolistedit'])) {
		if ($list_edit_request->response['status_uid'] == 'people_editlist_200') {
		?>
			<h3>Success</h3>
			<p>
			You're all edited and changed up. <a href=".././"><b>Back to lists</b></a>
			</p>
			<br />
		<?php } else { ?>
			<h3>Error</h3>
			<p>
			There was a problem. <a href="./">Please try again.</a>
			</p>
<?php 
		}
	}
	if ($current_list) {
?>
	<form method="post" action="">
		<input type="hidden" name="dolistedit" value="makeitso" />
		<div class="col_oneoftwo">
			<h3>List Details</h3>
			
			<label for="list_name">Name</label><br />
			<input type="text" id="list_name" name="list_name" value="<?php echo $current_list['name']; ?>" />

			<div class="row_seperator">.</div>

			<label for="settings_id">Connect To</label><br />
			<select id="settings_id" name="settings_id">
				<option value="0"<?php if ($current_list['settings_id'] == 0) { ?> selected="selected"<?php } ?>>None (local list only)</option>
				<?php AdminHelper::echoSettingsOptions('lists',$current_list['settings_id']) ?>
			</select>
		</div>

		<div class="col_oneoftwo lastcol">
			<h3>&nbsp;</h3>
			<label for="list_description">Description</label><br />
			<textarea rows="3" id="list_description" name="list_description"><?php echo $current_list['description']; ?></textarea>
		</div>

		<div class="row_seperator">.</div><br />
		<div>
			<input class="button" type="submit" value="Edit The List" />
		</div>

	</form>
<?php
	}
?>