<?php
if ($request_parameters) {
	if ($page_request->response['status_uid'] == 'element_getelement_200') {
		?>
		The embed code for this element is:
		</p>
		<code>
			&lt;?php cash_embedElement(<?php echo $page_request->response['payload']['id']; ?>); // CASH element (<?php echo $page_request->response['payload']['name'] . ' / ' . $page_request->response['payload']['type']; ?>) ?&gt;
		</code>
		<br /><br />
		Add usage statistics, embed locations, any other analytics...
		<?php
	} else {
		echo "There was a problem getting the element's details. Please <a href=\"" . ADMIN_WWW_BASE_PATH . "/elements/view/\">try again</a>.";
	}
} else {
	if ($page_request->response['status_uid'] != 'element_getelementsforuser_200') {
		echo "No elements were found. None. Zero. Zip. If you're looking to add one to the system, <a href=\"../add/\">go here</a>.";
	} else {
		$colcount = 1;
		foreach ($page_request->response['payload'] as $element => $data) {
			if ($colcount % 2 == 0) {
				$secondclass = ' lastcol';
			} else {
				$secondclass = '';
			}
			?>
			<div class="col_oneoftwo<?php echo $secondclass; ?>">
				<div class="callout">
					<h4><?php echo $data['name']; ?></h4>
					<?php
					if (array_key_exists($data['type'],$elements_data)) {
						echo '<b>' . $elements_data[$data['type']]->name . '</b> ';
					} else {
						//echo '<b>' . $data['type'] . '</b> ';
					}
					?>
					&nbsp; <span class="smalltext fadedtext nobr">created: <?php echo date('M jS, Y',$data['creation_date']); if ($data['modification_date']) { echo ' (modified: ' . date('F jS, Y',$data['modification_date']) . ')'; } ?></span>
					<div class="tar">
						<br />
						<a href="<?php echo $data['id']; ?>" class="mininav">Details</a> <a href="../edit/<?php echo $data['id']; ?>/" class="mininav">Edit</a> <a href="../delete/<?php echo $data['id']; ?>/" class="needsconfirmation mininav">Delete</a>
					</div>
				</div>
			</div>
			<?php
			/*
			if ($colcount % 2 == 0) {
				echo '<div class="row_seperator">.</div>';
			}
			*/
			$colcount++;
		}
	}
}
?>