<div class="col_oneoftwo">
	<h2>Add an event</h2>
	<label for="settings_id">Use Settings</label><br />
	<select id="settings_id" name="settings_id">
		<option value="0" selected="selected">None (Normal http:// link)</option>
		<?php AdminHelper::echoSettingsOptions('assets') ?>
	</select>
	
	<div class="row_seperator">.</div>

	<label for="asset_title">Title / Name</label><br />
	<input type="text" id="asset_title" name="asset_title" value="" placeholder="Give It A Name" />

	<label for="asset_location">Location (URI)</label><br />
	<input type="text" id="asset_location" name="asset_location" value="" placeholder="URL, S3 path, or SoundCloud URL" />
	
	<div class="row_seperator">.</div><br />
	<input class="button" type="submit" value="Add That Element" />
</div>
<div class="col_oneoftwo lastcol">
	<h2>All upcoming events</h2>
	<?php echo calendar_events_format_dates($cash_admin->getStoredResponse('events_allfuture')); ?>
</div>