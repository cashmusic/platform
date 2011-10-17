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
		<span class="highlightcopy">Add usage statistics, embed locations, any other analytics...</span>
		<?php
	} else {
		echo "There was a problem getting the element's details. Please <a href=\"" . ADMIN_WWW_BASE_PATH . "/elements/view/\">try again</a>.";
	}
} else {
	echo '<h3>All Defined Elements</h3><br />';
	if ($page_request->response['status_uid'] != 'element_getelementsforuser_200') {
		echo "No elements were found. None. Zero. Zip. If you're looking to add one to the system, <a href=\"../add/\">go here</a>.";
	} else {
		$loopcount = 1;
		echo '<ul class="alternating">';
		foreach ($page_request->response['payload'] as $element => $data) {
			$altclass = '';
			if ($loopcount % 2 == 0) { $altclass = ' class="alternate"'; }
			?>
			<li<?php echo $altclass; ?>>
					<h4>
						<?php 
							echo $data['name'];
							if (array_key_exists($data['type'],$elements_data)) {
								echo ' <small class="fadedtext nobr"> // ' . $elements_data[$data['type']]->name . '</small> ';
							}
						?>
					</h4>
					<div class="col_oneoftwo">
						<a href="<?php echo $data['id']; ?>" class="mininav_flush">Details</a> <a href="../edit/<?php echo $data['id']; ?>/" class="mininav_flush">Edit</a> <a href="../delete/<?php echo $data['id']; ?>/" class="needsconfirmation mininav_flush">Delete</a>
					</div>
					<div class="col_oneoftwo lastcol tar">
						<span class="smalltext fadedtext nobr">created: <?php echo date('M jS, Y',$data['creation_date']); if ($data['modification_date']) { echo ' (modified: ' . date('F jS, Y',$data['modification_date']) . ')'; } ?><br />accessed xxx times (xxx in the past 7 days)</span>
					</div>
					<div class="row_seperator">.</div>
			</li>
			<?php
			$loopcount++;
		}
		echo '</ul>';
	}
}
?>