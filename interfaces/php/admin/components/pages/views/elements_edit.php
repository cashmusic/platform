<div class="callout">
	<h3 class="fadedtext">Embed it</h3>
	<p class="fadedtext">To embed your element copy and paste one of these codes:</p>
	<b class="fadedtext">PHP: </b><code><input type="text" value="&lt;?php include('<?php echo CASH_PLATFORM_PATH; ?>');CASHSystem::embedElement(<?php echo $current_element['id']; ?>); // (<?php echo $current_element['name'] ?>) ?&gt;" /></code>
</div>

<div class="row_seperator">.</div>

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
	
	<?php
}
?>

<div class="row_seperator">.</div>
<br />

<?php
	if (isset($cash_admin->page_data['error_message'])) {
			echo '<p><span class="highlightcopy">' . $cash_admin->page_data['error_message'] . '</span></p>';
		}
	echo $element_rendered_content;
?>