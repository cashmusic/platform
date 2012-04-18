<?php
if ($all_order_details) {
	echo '<h3>All completed orders</h3>';
	?>
	<table style="width:100%;">
		<colgroup style="width:10%;" />
		<colgroup style="width:13%;" />
		<colgroup style="width:32%;" />
		<colgroup />
		<colgroup style="width:8%;" />
		<colgroup style="width:8%;" />
		<thead>
			<tr>
				<th scope="col">Order #</th>
				<th scope="col">Date</th>
				<th scope="col">Customer</th>
				<th scope="col">Items</th>
				<th scope="col" style="text-align:right;">Gross</th>
				<th scope="col" style="text-align:right;">Net</th>
			</tr>
		</thead>
		<tbody>
	<?php
	foreach ($all_order_details as $order) {
	    ?>
		<tr>
			<td><?php echo $order['id']; ?></td>
			<td><?php echo $order['date']; ?></td>
			<td><?php echo $order['customer']; ?></td>
			<td><?php echo $order['items']; ?></td>
			<td style="text-align:right;"><?php echo $order['gross']; ?></td>
			<td style="text-align:right;"><?php echo $order['net']; ?></td>
		</tr>
		<?php
	}
	?>
		</tbody>
	</table>
	<br />
	<?php
} else {
	echo '<h3>Sorry, no completed orders</h3>There are no completed orders right now.';
}
?>