<div class="col_oneoftwo">
	<h2>This week at a glance</h2>
	<?php echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('events_thisweek')); ?>
</div>
<div class="col_oneoftwo lastcol">
	<h2>Unpublished events</h2>
	<?php echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('events_unpublished')); ?>
</div>