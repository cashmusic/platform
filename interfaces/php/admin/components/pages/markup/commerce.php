<?php
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	echo '<div class="introductorybanner"><div class="titlespc"><h2>Commerce</h2><b>sell</b> your music<br />review and <b>fulfill</b> orders</div>';
	echo 'Here’s where you’ll define products and special offers, check on orders, manage fulfillment, and tracks overall sales. Connect to your Paypal account and off you go.';
	echo '<div class="closelink"><a href="?hidebanner=true">close <span class="icon x_alt"></span></a></div></div>';
}
?>

<div class="col_oneoftwo">
	<h2>Recent orders</h2>
	<p>
		Coming soon. See orders page for order details.
	</p>
</div>
<div class="col_oneoftwo lastcol">
	<h2>All items</h2>
	<?php echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('all_items'), false); ?>
</div>
