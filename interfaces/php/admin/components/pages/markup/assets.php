<?php
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	echo '<div class="introductorybanner"><div class="titlespc"><h2>Assets</h2><b>upload</b> files<br /><b>organize</b> assets for use<br />add <b>tags</b> and <b>metadata</b></div>';
	echo 'Enter details about all the files that matter to you, either on a connected S3 account or simple URLs. These assets will be used in the elements you define.';
	echo '<div class="closelink"><a href="?hidebanner=true">close <span class="icon x_alt"></span></a></div></div>';
}
?>

<div class="callout">
	<h3>Quick Asset Search</h3>
	<form method="post">
		<label for="text1">Title / Tags</label><br />
		<div class="col_threeoffour">
		<input type="text" id="text1" placeholder="Disabled" /> 
		</div>
		<div class="col_oneoffour lastcol">
		<input class="button" type="submit" style="width:136px !important;" value="Search" />
		</div>
	</form>
	<div class="row_seperator">.</div>
</div>

<div class="row_seperator">.</div>
<div class="col_oneoftwo">
	<h2>Most Accessed</h2>
		<?php
		$mostaccessed_response = $cash_admin->getStoredResponse('asset_mostaccessed');
		if (is_array($mostaccessed_response['payload'])) {
			$loopcount = 1;
			echo '<ol class="fadedtext">';
			foreach ($mostaccessed_response['payload'] as $asset) {
				echo '<li><a href="./assets/edit/single/' . $asset['id'] . '">' . $asset['title'] . '</a> <span class="smalltext nobr">(accessed: ' . $asset['count'] . ')</span></li>';
				$loopcount = $loopcount + 1;
				if ($loopcount == 3) { break; }
			}
			echo '</ol>';
		} else {
			echo '<p class="fadedtext">No assets have been accessed.</p>';
		}
		?>
</div><div class="col_oneoftwo lastcol">
	<h2>Recently Added</h2>
		<?php
		if (is_array($cash_admin->getStoredResponse('asset_recentlyadded',true))) {
			$loopcount = 1;
			echo '<ul class="alternating linkonly fadedtext">';
			foreach ($cash_admin->getStoredResponse('asset_recentlyadded',true) as $asset) {
				echo '<li><a href="./edit/single/' . $asset['id'] . '">' . $asset['title'] . '</a></li>';
				if ($loopcount == 5) { break; }
				$loopcount = $loopcount + 1;
			}
			echo '</ul>';
		} else {
			echo '<p>No assets have been added yet.</p>';
		}
		?>
</div>