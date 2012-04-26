<div class="col_oneoftwo">
	<h2>Add an event</h2>
	<p>
		Quickly add a date to the system. Information about published/unpublished status and
		cancellation can be found in the page tips.
	</p>
	
	<p><span class="highlightcopy">
		The date and venue selectors are far from ideal. We'll be enhancing these with fancy
		interfaces soon.
	</span></p>

	<form method="post" action="./add/" name="add_event">
		<input type="hidden" name="doeventadd" value="makeitso" />

		<label for="event_date">Date</label><br />
		<input type="text" id="event_date" name="event_date" value="" placeholder="MM/DD/YYYY" />

		<div class="row_seperator">.</div>

		<label for="event_venue">Venue</label><br />
		<select id="event_venue" name="event_venue">
			<option value="0" selected="selected">Unknown (Will be listed as 'TBA')</option>
			<?php AdminHelper::echoFormOptions('venues') ?>
		</select>

		<div class="row_seperator">.</div>
		<label for="event_comment">Comments (Support, CD Release, etc)</label><br />
		<textarea rows="3" id="event_comment" name="event_comment"></textarea>

		<div class="row_seperator">.</div>
		<label for="event_purchase_url">Ticket Purchase URL</label><br />
		<input type="text" id="event_purchase_url" name="event_purchase_url" value="" placeholder="Ticket Link URL" />

		<div class="row_seperator">.</div>
		<input type='checkbox' class='checkorradio' name='event_ispublished' value='' checked='checked' /> Published?
		&nbsp; <input type='checkbox' class='checkorradio' name='event_iscancelled' value='' /> Cancelled?

		<div class="row_seperator">.</div><br />
		<input class="button" type="submit" value="Add That Event" />
	
	</form>
</div>
<div class="col_oneoftwo lastcol">
	<h2>All upcoming events</h2>
	<?php echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('events_allfuture'), true); ?>
</div>
