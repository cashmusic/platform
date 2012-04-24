<?php 
	$current_event = $cash_admin->getStoredResponse('getevent', true);
	if (isset($_POST['doeventedit'])) {
		$event_edit_request = $cash_admin->getStoredResponse('eventeditattempt');
		if ($event_edit_request['status_uid'] == 'calendar_editevent_200') {
		?>
			<h3>Success</h3>
			<p>
			You're all edited and changed up. <a href=".././"><b>Back to events</b></a>
			</p>
			<br />
		<?php } else { ?>
			<h3>Error</h3>
			<p>
			There was a problem. <a href="./">Please try again.</a>
			</p>
<?php 
		}
	}
	if ($current_event) {
?>
	<h2>Event details</h2>
	<p>
		Quickly add a date to the system. Information about published/unpublished status and
		cancellation can be found in the page tips.
	</p>

	<p><span class="highlightcopy">
		The date and venue selectors are far from ideal. We'll be enhancing these with fancy
		interfaces soon.
	</span></p>

	<form method="post" action="">
		<input type="hidden" name="doeventedit" value="makeitso" />

		<label for="event_date">Date</label><br />
		<input type="text" id="event_date" name="event_date" value="<? echo date('m/j/Y h:iA T',$current_event['date']); ?>" />

		<div class="row_seperator">.</div>

		<label for="event_venue">Venue</label><br />
		<select id="event_venue" name="event_venue">
			<option value="0" selected="selected">Unknown (Will be listed as 'TBA')</option>
			<?php AdminHelper::echoFormOptions('venues',$current_event['venue_id']); ?>
		</select>

		<div class="row_seperator">.</div>
		<label for="event_comment">Comments (Support, CD Release, etc)</label><br />
		<textarea rows="3" id="event_comment" name="event_comment"><?php echo $current_event['comments']; ?></textarea>

		<div class="row_seperator">.</div>
		<label for="event_purchase_url">Ticket Purchase URL</label><br />
		<input type="text" id="event_purchase_url" name="event_purchase_url" value="<?php echo $current_event['purchase_url']; ?>" />

		<?php
		$published_check = '';
		$cancelled_check = '';
		if ($current_event['published']) { $published_check = " checked='checked'"; } 
		if ($current_event['cancelled']) { $cancelled_check = " checked='checked'"; } 
		?>
		<div class="row_seperator">.</div>
		<input type='checkbox' class='checkorradio' name='event_ispublished' value=''<?php echo $published_check; ?> /> Published?
		&nbsp; <input type='checkbox' class='checkorradio' name='event_iscancelled' value=''<?php echo $cancelled_check; ?> /> Cancelled?

		<div class="row_seperator">.</div><br />
		<input class="button" type="submit" value="Edit The Event" />

	</form>
<?php
	}
?>