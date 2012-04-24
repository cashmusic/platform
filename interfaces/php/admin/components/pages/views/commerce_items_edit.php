<?php 
	$current_event = $cash_admin->getStoredResponse('getitem', true);
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
<h2><?php echo $current_event['name']; ?></h2>

<form method="post" action="" name="edit_item">
	<input type="hidden" name="doitemedit" value="makeitso" />

	<label for="item_name">Name</label><br />
	<input type="text" id="item_name" name="item_name" value="<?php echo $current_event['name']; ?>" />

	<div class="row_seperator">.</div>
	<label for="item_description">Description</label><br />
	<textarea rows="3" id="item_description" name="item_description"><?php echo $current_event['description']; ?></textarea>

	<div class="row_seperator">.</div>
	<label for="item_price">Price ($ USD)</label><br />
	<input type="text" id="item_price" name="item_price" value="<?php echo $current_event['price']; ?>" />

	<div class="row_seperator">.</div>
	<label for="item_fulfillment_asset">Fulfillment asset</label><br />
	<select id="item_fulfillment_asset" name="item_fulfillment_asset">
		<?php AdminHelper::echoFormOptions('assets',$current_event['fulfillment_asset'],$cash_admin->getAllFavoriteAssets()); ?>
	</select>

	<div class="row_seperator">.</div><br />
	<input class="button" type="submit" value="Edit The Item" />

</form>
<?php
	}
?>