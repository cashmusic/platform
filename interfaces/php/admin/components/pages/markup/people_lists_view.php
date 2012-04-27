<?php
$list_details = $cash_admin->getStoredResponse('listdetails');
if (isset($list_details)) {	
	if ($list_details['status_uid'] == 'people_getlist_200') {
		$list_members = $cash_admin->getStoredResponse('listmembers',true);
		echo '<p>' . $list_members['details']['description'] . '</p>';
		
		echo '<h2>Actions</h2>';
		echo '<p>'
		. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/edit/' . $list_members['details']['id'] . '" class="mininav_flush"><span class="icon pen"></span> Edit</a> '
		. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/export/' . $list_members['details']['id'] . '" class="mininav_flush"><span class="icon download"></span> Export</a> '
		. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/delete/' . $list_members['details']['id'] . '" class="mininav_flush needsconfirmation"><span class="icon x_alt"></span> Delete</a>'
		. '</p>';

		$list_analytics = $cash_admin->getStoredResponse('listanalytics',true);
		echo '<h2>Statistics</h2>';
		echo '<div class="col_oneoftwo">'
		. 'Total active members: ' . $list_analytics['active'] . '<br />'
		. 'Inactive members: ' . $list_analytics['inactive'] . '<br />'
		. '</div><div class="col_oneoftwo lastcol">'
		. 'New members, past 7 days: ' . $list_analytics['last_week'] . '<br />'
		. '</div><div class="row_seperator">.</div>';

		if (is_array($list_members['members'])) {
			echo '<br /><h2>List Members</h2>';
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
			foreach ($list_members['members'] as $entry) {
				if ($entry['active']) {
			    ?>
				<tr>
					<td><a href="mailto:<?php echo $entry['email_address']; ?>"><?php echo $entry['email_address']; ?></a></td>
					<td><?php echo $entry['display_name']; ?></td>
					<td><?php echo date('M j, Y',$entry['creation_date']); ?></td>
					<td><?php echo $entry['initial_comment']; ?></td>
				</tr>
				<?php
				}
			}
			?>
				</tbody>
			</table>
			<?php
		}
	} else {
		echo '<h2>Sorry</h2>';
		echo "Error getting list.";
	}
}

?>