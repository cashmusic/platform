<h3>Quick Asset Search</h3>
<form method="post">
	<label for="text1">Title / Tags</label><br />
	<div class="col_onethird">
	<input type="text" id="text1" placeholder="Disabled" /> 
	</div>
	<div class="col_onethird">
	<input class="button" type="submit" value="Search" />
	</div>
</form>

<br /><br /><br /><br />
<div class="col_onehalf">
	<h2>Most Accessed</h2>
		<?php
		if (!is_array($page_data['asset_mostaccessed'])) {
			$loopcount = 1;
			echo '<ol class="fadedtext">';
			foreach ($page_data['asset_mostaccessed'] as $asset) {
				echo '<li><a href="#">' . $asset['title'] . '</a> <span class="smalltext nobr">(accessed: ' . $asset['count'] . ')</span></li>';
				$loopcount = $loopcount + 1;
				if ($loopcount == 6) { break; }
			}
			echo '</ol>';
		} else {
			echo '<p>No assets have been accessed yet.</p>';
		}
		?>
</div><div class="col_onehalf lastcol">
	<h2>Recently Added</h2>
	<?php
	if (!is_array($page_data['asset_recentlyadded'])) {
		$loopcount = 1;
		echo '<ul class="nobullets fadedtext">';
		foreach ($page_data['asset_recentlyadded'] as $asset) {
			echo '<li><a href="#">' . $asset['title'] . '</a></li>';
			if ($loopcount == 6) { break; }
			$loopcount = $loopcount + 1;
		}
		echo '</ul>';
	} else {
		echo '<p>No assets have been added yet.</p>';
	}
	?>
</div>