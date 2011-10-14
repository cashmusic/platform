<div class="col_onehalf">
	<h2>This week at a glance</h2>
	<?php echo calendar_format_dates($cash_admin->getStoredResponse('events_thisweek')); ?>
</div>
<div class="col_onehalf lastcol">
	<h2>Unpublished events</h2>
	<?php echo calendar_format_dates($cash_admin->getStoredResponse('events_unpublished')); ?>
</div>