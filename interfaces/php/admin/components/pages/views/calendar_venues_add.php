<?php if (!isset($_POST['dovenueadd'])) { ?>
	<h2>Add a venue</h2>
	<form method="post" action="" name="venue_add">
		<input type="hidden" name="dovenueadd" value="makeitso" />

		<label for="venue_name">Name</label><br />
		<input type="text" id="venue_name" name="venue_name" value="" placeholder="Venue Name" />

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">
			<label for="venue_url">Website</label><br />
			<input type="text" id="venue_url" name="venue_url" value="" placeholder="Website" />
		</div>
		<div class="col_oneoftwo lastcol">
			<label for="venue_phone">Phone</label><br />
			<input type="text" id="venue_phone" name="venue_phone" value="" placeholder="Phone Number" />
		</div>

		<div class="row_seperator">.</div>
		<label for="venue_address1">Address 1</label><br />
		<input type="text" id="venue_address1" name="venue_address1" value="" placeholder="Address (1)" />
		
		<div class="row_seperator">.</div>
		<label for="venue_address2">Address 2</label><br />
		<input type="text" id="venue_address2" name="venue_address2" value="" placeholder="Address (2)" />
		
		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">
			<label for="venue_city">City</label><br />
			<input type="text" id="venue_city" name="venue_city" value="" placeholder="City" />
		</div>
		<div class="col_oneoftwo lastcol">
			<label for="venue_region">State / Provence / Region</label><br />
			<input type="text" id="venue_region" name="venue_region" value="" placeholder="Region" />
		</div>

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">
			<label for="venue_postalcode">Postal Code</label><br />
			<input type="text" id="venue_postalcode" name="venue_postalcode" value="" placeholder="Postal Code" />
		</div>
		<div class="col_oneoftwo lastcol">
			<label for="venue_country">Country</label><br />
			<select id="venue_country" name="venue_country">
				<?php echo AdminHelper::drawCountryCodeUL(); ?>
			</select>
		</div>

		<div class="row_seperator">.</div><br />
		<input class="button" type="submit" value="Add That Venue" />
	
	</form>
<?php } else {
	$add_venue_response = $cash_admin->getStoredResponse('venueaddattempt');
	if ($add_venue_response['status_uid'] == 'calendar_addvenue_200') {
	?>

		<h3>Success</h3>
		<p>
		The new venue has been added
		</p>
		<a href="../"><b>Add another?</b></a>
		<br />

	<?php } else { ?>

		<h3>Error</h3>
		<p>
		There was a problem adding the event. <a href="../">Please try again.</a>
		</p>

<?php 
	}
}	
?>
