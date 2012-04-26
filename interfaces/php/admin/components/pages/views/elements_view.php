<?php
if ($request_parameters) {
	if ($page_request->response['status_uid'] == 'element_getelement_200') {
		?>
		The embed code for this element is:
		</p>
		<code>
			&lt;?php CASHSystem::embedElement(<?php echo $page_request->response['payload']['id']; ?>); // CASH element (<?php echo $page_request->response['payload']['name']; ?>) ?&gt;
		</code>
		<br />
		
		<?php
		$location_analytics = $cash_admin->getStoredResponse('elementbylocation',true);
		$total_views = 0;
		if (is_array($location_analytics)) {
			foreach ($location_analytics as $entry) {
				$total_views = $total_views + $entry['total'];
			}
		}
		echo '<h2>Statistics</h2>' . 'Total unique views: ' . $total_views . '<br />';

		if (is_array($location_analytics)) {
			echo '<br /><h2>Views by location</h2>';
			?>
			<table style="width:100%;">
				<colgroup style="width:85%;" /><colgroup />	
				<thead>
					<tr>
						<th scope="col">Element location</th>
						<th scope="col">Views</th>
					</tr>
				</thead>
				<tbody>
			<?php
			foreach ($location_analytics as $entry) {
			    ?>
				<tr>
					<td><?php echo $entry['access_location']; ?></td>
					<td><?php echo $entry['total']; ?></td>
				</tr>
				<?php
			}
			?>
				</tbody>
			</table>
			<br />
			<?php
		}
		
		$method_analytics = $cash_admin->getStoredResponse('elementbymethod',true);
		if (is_array($method_analytics)) {
			echo '<br /><h2>Views by request method</h2>';
			?>
			<table style="width:100%;">
				<colgroup style="width:85%;" /><colgroup />	
				<thead>
					<tr>
						<th scope="col">Request method</th>
						<th scope="col">Views</th>
					</tr>
				</thead>
				<tbody>
			<?php
			foreach ($method_analytics as $entry) {
				$methods_string = array ('direct','api_public','api_key','api_fullauth');
				$methods_translation = array('direct (embedded on this site)','api_public (shared to another site)','api_key (shared to another site)','api_fullauth (another site with your API credentials)');
			    ?>
				<tr>
					<td><?php echo str_replace($methods_string,$methods_translation,$entry['access_method']); ?></td>
					<td><?php echo $entry['total']; ?></td>
				</tr>
				<?php
			}
			?>
				</tbody>
			</table>
			<br />
			<?php
		}
	} else {
		echo "There was a problem getting the element's details. Please <a href=\"" . ADMIN_WWW_BASE_PATH . "/elements/view/\">try again</a>.";
	}
} else {
	echo '<h3>All Defined Elements</h3><br />';
	echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('getelementsforuser'));
}
?>