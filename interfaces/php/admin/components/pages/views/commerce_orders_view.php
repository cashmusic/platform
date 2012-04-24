<h3>Order details</h3>
<div class="col_oneoftwo">
	<b>Order number:</b> <?php echo str_pad($order_details['id'],6,0,STR_PAD_LEFT); ?> <br />
	<b>Placed on: </b><?php echo $order_details['order_date']; ?> <br />
	<b>Fulfilled: </b><?php if ($order_details['fulfilled']) { echo 'yes'; } else { echo 'no'; } ?><br />
	<b>Payment status: </b><?php echo $order_details['status']; ?> <br /><br />
	<b>Order total: </b><?php echo '$' . sprintf("%01.2f",$order_details['gross_price']); ?> <br />
	<b>Total minus fees: </b> <?php echo '$' . sprintf("%01.2f",$order_details['gross_price'] - $order_details['service_fee']); ?>
</div>
<div class="col_oneoftwo lastcol">
	<b>Customer:</b><br />
	<?php 
		echo $order_details['customer_details']['display_name'] . '<br />'; 
		echo '<a href="mailto:' . $order_details['customer_details']['email_address'] . '">' . $order_details['customer_details']['email_address'] . '</a>';
	?>
	<br /><br /><b>Country: </b> <?php echo $order_details['customer_details']['address_country']; ?>
</div>


<div class="row_seperator tall">.</div><br />
<?php
$order_contents = json_decode($order_details['order_contents'],true);
if (is_array($order_contents)) {
?>
	<h3>Items included</h3>
	<table style="width:100%;">
		<colgroup style="width:8%;" />
		<colgroup style="width:25%;" />
		<colgroup />
		<colgroup style="width:8%;" />
		<thead>
			<tr>
				<th scope="col">Qty</th>
				<th scope="col">Name</th>
				<th scope="col">Description</th>
				<th scope="col" style="text-align:right;">Price</th>
			</tr>
		</thead>
		<tbody>
	<?php
	foreach ($order_contents as $item) {
	    ?>
		<tr>
			<td>1</td>
			<td><a href="<?php echo ADMIN_WWW_BASE_PATH . '/commerce/items/edit/' . $item['id']; ?>"><?php echo $item['name']; ?></a></td>
			<td><?php echo $item['description']; ?></td>
			<td style="text-align:right;">$<?php echo $item['price']; ?></td>
		</tr>
		<?php
	}
	?>
		</tbody>
	</table>
	<br />
<?php } ?>

<div class="row_seperator">.</div><br />
<h3>Transaction details</h3>
<b>Connection used: </b> <?php echo AdminHelper::getConnectionName($order_details['connection_id']) . ' (' . $order_details['connection_type'] . ')' ?> <br />
<b>Transaction ID: </b> <?php echo $order_details['service_transaction_id'] ?><br />
<b>Service fees: </b> $<?php echo $order_details['service_fee'] ?><br />
<br />
<div class="col_oneoftwo">
	<b>Data sent to service</b>
	<ul class="smalltext">
	<?php
	foreach (json_decode($order_details['data_sent'],true) as $key => $value) {
		echo '<li><b>' . $key . ':</b> ' . $value . '</li>';
	}
	?>
	</ul>
</div>
<div class="col_oneoftwo lastcol">
	<b>Data returned by service</b>
	<ul class="smalltext">
	<?php
	foreach (json_decode($order_details['data_returned'],true) as $key => $value) {
		echo '<li><b>' . $key . ':</b> ' . $value . '</li>';
	}
	?>
	</ul>
</div>
