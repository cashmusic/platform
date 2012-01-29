<?php
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	echo '<div class="introductorybanner"><div class="titlespc"><h2>Calendar</h2>manage your <b>contacts</b><br />create and maintain <b>lists</b><br />monitor <b>social</b> media</div>';
	echo 'Enter all your shows, manage your calendar and your guestlists, and use all of it to feed elements like show listings, tour archives, and even guestlist slot giveaways.';
	echo '<div class="closelink"><a href="?hidebanner=true">close <span class="icon x_alt"></span></a></div></div>';
}
?>

<div class="col_oneoftwo">
	<h2>This week at a glance</h2>
	<?php echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('events_thisweek')); ?>
</div>
<div class="col_oneoftwo lastcol">
	<h2>Unpublished events</h2>
	<?php echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('events_unpublished')); ?>
</div>