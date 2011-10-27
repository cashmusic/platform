<?php if (!isset($_POST['doassetadd'])) { ?>
	<form method="post" action="">
		<input type="hidden" name="doassetadd" value="makeitso" />

		<h3>Asset Details</h3>
		<label for="asset_title">Title / Name</label><br />
		<input type="text" id="asset_title" name="asset_title" value="" placeholder="Give It A Name" />

		<div class="row_seperator">.</div>
		<label for="asset_description">Description</label><br />
		<textarea rows="3" id="asset_description" name="asset_description"></textarea>

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">
			<label for="settings_id">Use Settings</label><br />
			<select id="settings_id" name="settings_id">
				<option value="0" selected="selected">None (Normal http:// link)</option>
				<?php AdminHelper::echoSettingsOptions('assets') ?>
			</select>
		</div>

		<div class="col_oneoftwo lastcol">
			<label for="asset_location">Location (URI)</label><br />
			<input type="text" id="asset_location" name="asset_location" value="" placeholder="URL, S3 path, or SoundCloud URL" />
		</div>

		<div class="row_seperator">.</div><br />

		<div class="col_oneoftwo">
			<div>
				<label>Tags</label><br />
				<a href="#" class="injectbefore" rev="<input type='text' name='tag' value='' placeholder='Tag' />"><small>+ ADD TAG</small></a>
			</div>
		</div>

		<div class="col_oneoftwo lastcol">
			<div>
				<label>Metadata (Advanced)</label><br />
				<a href="#" class="injectbefore" rev="<input type='text' name='metadatakey' value='' placeholder='Key (Data Type)' /><br /><input type='text' name='metadatavalue' value='' placeholder='Value' /><br /><br />"><small>+ ADD CUSTOM METADATA</small></a>
			</div>
		</div>
		
		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Add That Element" />
		</div>

	</form>
		
<?php } else {
	$asset_add_request = $cash_admin->getStoredResponse('addasset');
	if ($asset_add_request['status_uid'] == 'asset_addasset_200') {	
	?>
	
		<h3>Success</h3>
		<p>
		The new asset is in the system and ready to use.
		</p>
		<a href="./"><b>Add another.</b></a>
		<br />

	<?php } else { ?>
		
		<h3>Error</h3>
		<p>
		There was a problem adding the asset. <a href="./">Please try again.</a>
		</p>

<?php 
	}
}	
?>