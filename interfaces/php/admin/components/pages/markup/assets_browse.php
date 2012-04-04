<?php
$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
$applicable_connections = $page_data_object->getConnectionsByScope('assets');

if (is_array($applicable_connections)) {
	echo "<h2>Stored Files</h2><ul>\n";
	foreach ($applicable_connections as $connection) {
		echo '<li>';
		echo "<a href=\"connection/{$connection['id']}/\">{$connection['name']}</a>";
		echo ' <span class="smalltext fadedtext nobr">updated: ' . date('M jS, Y',$connection['modification_date']) . '</span>';
		
		$assets_reponse = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'getassetsforconnection',
				'connection_id' => $connection['id']
			),
			'allassets'
		);
		if (is_array($assets_reponse)) {
			echo '<ul>';
			foreach ($assets_reponse['payload'] as $asset) {
				echo '<li><a href="' . ADMIN_WWW_BASE_PATH . '/assets/edit/single/' . $asset['id'] . '">' . $asset['location'] . '</a></li>';
			}
			echo '</ul>';
		}
		
		echo '</li>';
	}
	echo "</ul>\n";
}
?>