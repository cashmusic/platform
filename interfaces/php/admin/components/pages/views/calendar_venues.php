<div class="col_oneoftwo">
	<h2>Quick add a venue</h2>
	<p>
		Quickly add a venue with just the basics.
	</p>

	<form method="post" action="./add/" name="quick_add_venue">
		<input type="hidden" name="dovenueadd" value="makeitso" />

		<label for="venue_name">Name</label><br />
		<input type="text" id="venue_name" name="venue_name" value="" placeholder="Venue Name" />
		
		<div class="row_seperator">.</div>
		<label for="venue_city">City</label><br />
		<input type="text" id="venue_city" name="venue_city" value="" placeholder="City" />
		
		<div class="row_seperator">.</div>
		<label for="venue_region">State / Provence / Region</label><br />
		<input type="text" id="venue_region" name="venue_region" value="" placeholder="Region" />

		<div class="row_seperator">.</div>

		<label for="venue_country">Country</label><br />
		<select id="venue_country" name="venue_country">
			<?php echo AdminHelper::drawCountryCodeUL(); ?>
		</select>

		<div class="row_seperator">.</div><br />
		<input class="button" type="submit" value="Add That Venue" />
	
	</form>
</div>
<div class="col_oneoftwo lastcol">
	<h2>Add a venue</h2>
	<p>
		<a href="./add/">Add a venue with all details.</a>
	</p>
	
	<div class="row_seperator">.</div>
	<h2>All venues</h2>
	<?php echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('getallvenues'), true); ?>
</div>
