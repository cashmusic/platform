<div class="col_oneoftwo">
	<h2>Your Lists</h2>
	<?php
	if (!is_array($cash_admin->getStoredData('alllists'))) {
		echo "No lists were found. Sorry.";
	} else {
		foreach ($cash_admin->getStoredData('alllists') as $list) {
			?>
			<div class="callout">
				<h4><?php echo $list['name']; ?></h4>
				<?php echo $list['description']; ?><br />
				<span class="smalltext fadedtext nobr">Created: <?php echo date('M jS, Y',$list['creation_date']); if ($list['modification_date']) { echo ' (Modified: ' . date('F jS, Y',$list['modification_date']) . ')'; } ?></span>
				<div class="tar">
					<br />
					<a href="<?php echo ADMIN_WWW_BASE_PATH . '/people/mailinglists/view/' . $list['id']; ?>" class="mininav_spaced">View</a> <a href="<?php echo ADMIN_WWW_BASE_PATH . '/people/mailinglists/view/' . $list['id']; ?>" class="mininav_spaced">Edit</a> <a href="<?php echo ADMIN_WWW_BASE_PATH . '/people/mailinglists/view/' . $list['id']; ?>" class="mininav_spaced">Export</a> <a href="<?php echo ADMIN_WWW_BASE_PATH . '/people/mailinglists/view/' . $list['id']; ?>" class="mininav_spaced">Delete</a>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>
<div class="col_oneoftwo lastcol">
	<h2>Add A New List</h2>
	<form method="post" action="<?php echo ADMIN_WWW_BASE_PATH . '/people/mailinglists/add/'; ?>">
		<input type="hidden" name="dolistadd" value="makeitso" />

		<label for="list_name">Name</label><br />
		<input type="text" id="list_name" name="list_name" value="" placeholder="Give It A Name" />

		<div class="row_seperator">.</div>
		<label for="list_description">Description</label><br />
		<textarea rows="3" id="list_description" name="list_description"></textarea>

		<div class="row_seperator">.</div>
		<label for="settings_id">Connect To</label><br />
		<select id="settings_id" name="settings_id">
			<option value="0" selected="selected">None (local list only)</option>
			<?php AdminHelper::echoSettingsOptions('lists') ?>
		</select>

		<div class="row_seperator">.</div><br />
		<div>
			<input class="button" type="submit" value="Add The New List" />
		</div>

	</form>
</div>