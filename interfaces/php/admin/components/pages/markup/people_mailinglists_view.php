<style type="text/css">
tr:nth-child(odd) {
    background-color:#eee;
}
td, th {padding:2px 5px 2px 5px;vertical-align:top;}
th {background-color:#000;color:#fff;}
* #pagecontent td a,
div.usecolor1 #pagecontent td a, 
div.usecolor2 #pagecontent td a, 
div.usecolor3 #pagecontent td a, 
div.usecolor4 #pagecontent td a, 
div.usecolor5 #pagecontent td a {color:#333;}
</style>

<?php
if (isset($page_request->response)) {
	if ($page_request->response['status_uid'] == 'people_viewlist_200') {
		if (is_array($page_request->response['payload']))
		echo '<h2>List Details</h2>';
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
		foreach ($page_request->response['payload'] as $entry) {
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
} else {
	echo '<h2>Sorry</h2>';
	echo "No list. Please select one. [List lists...]";
}

?>