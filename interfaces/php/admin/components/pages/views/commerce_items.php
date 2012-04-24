<div class="col_oneoftwo">
	<h2>Add an item</h2>
	<p>
		Quickly add a digital item to the system.
	</p>

	<form method="post" action="./edit/" name="add_item">
		<input type="hidden" name="doitemadd" value="makeitso" />

		<label for="item_name">Name</label><br />
		<input type="text" id="item_name" name="item_name" value="" placeholder="Give it a name, please" />

		<div class="row_seperator">.</div>
		<label for="item_description">Description</label><br />
		<textarea rows="3" id="item_description" name="item_description"></textarea>

		<div class="row_seperator">.</div>
		<label for="item_price">Price ($ USD)</label><br />
		<input type="text" id="item_price" name="item_price" value="" placeholder="0.00" />

		<div class="row_seperator">.</div>
		<label for="item_fulfillment_asset">Fulfillment asset</label><br />
		<select id="item_fulfillment_asset" name="item_fulfillment_asset">
			<?php AdminHelper::echoFormOptions('assets',false,$cash_admin->getAllFavoriteAssets()); ?>
		</select>

		<div class="row_seperator">.</div><br />
		<input class="button" type="submit" value="Add The Item" />
	
	</form>
</div>
<div class="col_oneoftwo lastcol">
	<h2>All items</h2>
	<?php echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('all_items'), false); ?>
</div>
