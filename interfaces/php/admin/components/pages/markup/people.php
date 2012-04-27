<?php
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	echo '<div class="introductorybanner"><div class="titlespc"><h2>People</h2>manage your <b>contacts</b><br />create and maintain <b>lists</b><br />monitor <b>social</b> media</div>';
	echo 'Manage contacts on an individual basis or define lists to use for private login lists, mailing lists, etc. ';
	echo '<div class="closelink"><a href="?hidebanner=true">close <span class="icon x_alt"></span></a></div></div>';
}
?>

<div class="col_oneoftwo">
	<h2>Lists</h2>
	<?php echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('getlistsforuser')); ?>
</div>
<div class="col_oneoftwo lastcol">
	<h2>Social</h2>
	<p>
		Twitter and Facebook integrations not set up.
	</p>
</div>