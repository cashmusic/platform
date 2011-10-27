<?php
	$current_asset = $cash_admin->getStoredResponse('getasset',true);
	
	if(isset($_POST['doassetedit'])) {
		$asset_edit_request = $cash_admin->getStoredResponse('asseteditattempt');
		if ($asset_edit_request['status_uid'] == 'asset_editasset_200') {	
		?>
	
			<h3>Success</h3>
			<p>
			Your changes have been made.
			</p>

		<?php } else { ?>
		
			<h3>Error</h3>
			<p>
			There was a problem adding the asset. <a href="./">Please try again.</a>
			</p>
		
	<?php 
		}
	}
	if ($current_asset) {
?>

	<form method="post" action="">
		<input type="hidden" name="doassetedit" value="makeitso" />

		<h3>Asset Details</h3>
		<label for="asset_title">Title / Name</label><br />
		<input type="text" id="asset_title" name="asset_title" value="<?php echo str_replace('"','&quot;',$current_asset['title']); ?>" />

		<div class="row_seperator">.</div>
		<label for="asset_description">Description</label><br />
		<textarea rows="3" id="asset_description" name="asset_description"><?php echo str_replace('"','&quot;',$current_asset['description']); ?></textarea>

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">
			<label for="settings_id">Use Settings</label><br />
			<select id="settings_id" name="settings_id">
				<option value="0" selected="selected">None (Normal http:// link)</option>
				<?php AdminHelper::echoSettingsOptions('assets', $current_asset['settings_id']) ?>
			</select>
		</div>

		<div class="col_oneoftwo lastcol">
			<label for="asset_location">Location (URI)</label><br />
			<input type="text" id="asset_location" name="asset_location" value="<?php echo str_replace('"','&quot;',$current_asset['location']); ?>" />
		</div>

		<div class="row_seperator">.</div><br />

		<div class="col_oneoftwo">
			<div>
				<label>Tags</label><br />
				<?php
				$tag_counter = 1;
				foreach ($current_asset['tags'] as $tag) {
					echo "<input type='text' name='tag$tag_counter' value='$tag' placeholder='Tag' />";
					$tag_counter = $tag_counter+1;
				}
				?>
				<a href="#" class="injectbefore" rev="<input type='text' name='tag' value='' placeholder='Tag' />" rel="<?php echo $tag_counter; ?>"><small>+ ADD TAG</small></a>
			</div>
		</div>

		<div class="col_oneoftwo lastcol">
			<div>
				<label>Metadata (Advanced)</label><br />
				<?php
				$metadata_counter = 1;
				foreach ($current_asset['metadata'] as $type => $value) {
					echo "<input type='text' name='metadatakey$metadata_counter' value='$type' placeholder='Key (Data Type)' /><br /><input type='text' name='metadatavalue$metadata_counter' value='$value' placeholder='Value' /><br /><br />";
					$metadata_counter = $metadata_counter+1;
				}
				?>
				<a href="#" class="injectbefore" rev="<input type='text' name='metadatakey' value='' placeholder='Key (Data Type)' /><br /><input type='text' name='metadatavalue' value='' placeholder='Value' /><br /><br />" rel="<?php echo $metadata_counter; ?>"><small>+ ADD CUSTOM METADATA</small></a>
			</div>
		</div>
		
		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Edit The Element" />
		</div>

	</form>
	
<?php
}
?>