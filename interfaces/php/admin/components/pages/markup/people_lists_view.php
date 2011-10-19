<style type="text/css">
tr:nth-child(odd) {
    background-color:#eee;
}
td, th {padding:6px 5px 6px 5px;vertical-align:top;}
th {background-color:#000;color:#fff;}
* #pagecontent td a,
div.usecolor1 #pagecontent td a, 
div.usecolor2 #pagecontent td a, 
div.usecolor3 #pagecontent td a, 
div.usecolor4 #pagecontent td a, 
div.usecolor5 #pagecontent td a {color:#333;}
</style>

<?php
$list_details = $cash_admin->getStoredResponse('listdetails');
if (isset($list_details)) {	
	if ($list_details['status_uid'] == 'people_viewlist_200') {
		echo '<p>' . $list_details['payload']['details']['description'] . '</p>';
		
		echo '<h2>Actions</h2>';
		echo '<p>'
		. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/edit/' . $list_details['payload']['details']['id'] . '" class="mininav_flush">Edit</a> '
		. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/export/' . $list_details['payload']['details']['id'] . '" class="mininav_flush">Export</a> '
		. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/delete/' . $list_details['payload']['details']['id'] . '" class="mininav_flush needsconfirmation">Delete</a>'
		. '</p>';
		
		echo '<h2>Statistics</h2>';
		echo '<div class="col_oneoftwo">'
		. 'Total active members: ' . '7365' . '<br />'
		. 'New members in the past 7 days: ' . '712' . '<br />'
		. '</div><div class="col_oneoftwo lastcol">'
		// if MC Sync
		. 'Mailchimp campaigns sent to this list: ' . '2' . '<br />'
		. 'Average bounce rate: ' . '7%' . '<br />'
		. 'Average open rate: ' . '57%' . '<br />'
		. '</div><div class="row_seperator">.</div>';
		
		if (is_array($list_details['payload']['members']))
		echo '<h2>List Members</h2>';
		?>
		<table style="width:100%;">
			<colgroup style="width:32%;" />
			<colgroup style="width:23%;" />
			<colgroup style="width:12%;" />
			<colgroup />	

			<thead>
				<tr>
					<th scope="col">Email</th>
					<th scope="col">Name</th>
					<th scope="col">Joined</th>
					<th scope="col">Comment</th>
				</tr>
			</thead>

			<tbody>
		<?php
		foreach ($list_details['payload']['members'] as $entry) {
		    ?>
			<tr>
				<td><a href="mailto:<?php echo $entry['email_address']; ?>"><?php echo $entry['email_address']; ?></a></td>
				<td><?php echo $entry['display_name']; ?></td>
				<td><?php echo date('M j, Y',$entry['creation_date']); ?></td>
				<td><?php echo $entry['initial_comment']; ?></td>
			</tr>
			<?php
		}
		?>
			</tbody>
		</table>
		<?php
	} else {
		echo '<h2>Sorry</h2>';
		echo "Error getting list.";
	}
}

?>