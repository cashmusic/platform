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
			<div class="col_onehalf<?php echo $secondclass; ?>">
				<h3><?php echo $data['name']; ?></h3>
				<?php
				if (array_key_exists($data['type'],$elements_data)) {
					echo '<b>' . $elements_data[$data['type']]->name . '</b><br />';
				} else {
					//echo '<b>' . $data['type'] . '</b><br />';
				}
				?>
				Created: <?php echo date('F jS, Y',$data['creation_date']); if ($data['modification_date']) { echo ' (Modified: ' . date('F jS, Y',$data['modification_date']) . ')'; } ?>
				<br />
				<a href="<?php echo $data['id']; ?>" style="margin-right:1em;">Details</a> <a href="../edit/<?php echo $data['id']; ?>/" style="margin-right:1em;">Edit</a> <a href="../delete/<?php echo $data['id']; ?>/">Delete</a>
			</div>
			<?php
			if ($colcount % 2 == 0) {
				echo '<div class="row_seperator tall">.</div>';
			}
			$colcount++;
		}
	}
}
?>