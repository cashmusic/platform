<div class="col_oneoftwo">
	<h2>Your Lists</h2>
	<?php echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('getlistsforuser')); ?>
</div>
<div class="col_oneoftwo lastcol">
	<h2>Add A New List</h2>
	<form method="post" action="<?php echo ADMIN_WWW_BASE_PATH . '/people/lists/add/'; ?>" id="lists">
		<input type="hidden" name="dolistadd" value="makeitso" />

		<label for="list_name">Name</label><br />
		<input type="text" id="list_name" name="list_name" value="" placeholder="Give It A Name" />

		<div class="row_seperator">.</div>
		<label for="list_description">Description</label><br />
		<textarea rows="3" id="list_description" name="list_description"></textarea>

		<div class="row_seperator">.</div>
		<label for="connection_id">Connect To</label><br />
		<select id="connection_id" name="connection_id">
			<option value="0" selected="selected">None (local list only)</option>
			<?php AdminHelper::echoSettingsOptions('lists') ?>
		</select>

		<div class="row_seperator">.</div><br />
		<div>
			<input class="button" type="submit" value="Add The New List" />
		</div>

	</form>
</div>
