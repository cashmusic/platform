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
		if ($mostaccessed_response['status_uid'] == 'asset_getanalytics_200') {
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