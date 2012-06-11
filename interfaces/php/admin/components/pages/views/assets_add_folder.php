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
		<div>
			<br />
			<input class="button" type="submit" value="Add That Asset" />
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