<?php
	$current_venue = $cash_admin->getStoredResponse('getvenue',true);
	
	if(isset($_POST['dovenueedit'])) {
		$venue_edit_request = $cash_admin->getStoredResponse('venueeditattempt');
		if ($venue_edit_request['status_uid'] == 'calendar_editvenue_200') {	
		?>
	
			<h3>Success</h3>
			<p>
			Your changes have been made.
			</p>

		<?php } else { ?>
		
			<h3>Error</h3>
			<p>
			There was a problem editing the venue. <a href="./">Please try again.</a>
			</p>
		
	<?php 
		}
	}
	if ($current_venue) {
?>

<h2>Venue Details</h2>
<form method="post" action="" name="venue_add">
	<input type="hidden" name="dovenueedit" value="makeitso" />

	<label for="venue_name">Name</label><br />
	<input type="text" id="venue_name" name="venue_name" value="<?php echo str_replace('"','&quot;',$current_venue['name']); ?>" />

	<div class="row_seperator">.</div>
	<div class="col_oneoftwo">
		<label for="venue_url">Website</label><br />
		<input type="text" id="venue_url" name="venue_url" value="<?php echo $current_venue['url']; ?>" />
	</div>
	<div class="col_oneoftwo lastcol">
		<label for="venue_phone">Phone</label><br />
		<input type="text" id="venue_phone" name="venue_phone" value="<?php echo $current_venue['phone']; ?>" />
	</div>

	<div class="row_seperator">.</div>
	<label for="venue_address1">Address 1</label><br />
	<input type="text" id="venue_address1" name="venue_address1" value="<?php echo $current_venue['address1']; ?>" />
	
	<div class="row_seperator">.</div>
	<label for="venue_address2">Address 2</label><br />
	<input type="text" id="venue_address2" name="venue_address2" value="<?php echo $current_venue['address2']; ?>" />
	
	<div class="row_seperator">.</div>
	<div class="col_oneoftwo">
		<label for="venue_city">City</label><br />
		<input type="text" id="venue_city" name="venue_city" value="<?php echo $current_venue['city']; ?>" />
	</div>
	<div class="col_oneoftwo lastcol">
		<label for="venue_region">State / Provence / Region</label><br />
		<input type="text" id="venue_region" name="venue_region" value="<?php echo $current_venue['region']; ?>" />
	</div>

	<div class="row_seperator">.</div>
	<div class="col_oneoftwo">
		<label for="venue_postalcode">Postal Code</label><br />
		<input type="text" id="venue_postalcode" name="venue_postalcode" value="<?php echo $current_venue['postalcode']; ?>" />
	</div>
	<div class="col_oneoftwo lastcol">
		<label for="venue_country">Country</label><br />
		<select id="venue_country" name="venue_country">
			<?php echo AdminHelper::drawCountryCodeUL($current_venue['country']); ?>
		</select>
	</div>

	<div class="row_seperator">.</div><br />
	<input class="button" type="submit" value="Edit The Venue" />

</form>

<?php
}
?>