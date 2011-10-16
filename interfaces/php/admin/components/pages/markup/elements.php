<div class="col_oneoftwo">
	<h2>Most Active Elements</h2>
	<?php
	if ($page_data['element_mostactive']) {
		$loopcount = 1;
		echo '<ol class="fadedtext">';
		foreach ($page_data['element_mostactive'] as $element) {
			echo '<li><a href="' . ADMIN_WWW_BASE_PATH . '/elements/view/' . $element['id'] . '">' . $element['name'] . '</a> <span class="smalltext nobr">(accessed: ' . $element['count'] . ')</span></li>';
			$loopcount = $loopcount + 1;
			if ($loopcount == 6) { break; }
		}
		echo '</ol>';
	} else {
		echo '<p>No elements have been accessed yet.</p>';
	}
	?>
	<h2>Recently Added Elements</h2>
	<?php
	if ($page_data['element_recentlyadded']) {
		$loopcount = 1;
		echo '<ul class="nobullets fadedtext">';
		foreach ($page_data['element_recentlyadded'] as $element) {
			echo '<li><a href="' . ADMIN_WWW_BASE_PATH . '/elements/view/' . $element['id'] . '">' . $element['name'] . '</a></li>';
			if ($loopcount == 6) { break; }
			$loopcount = $loopcount + 1;
		}
		echo '</ul>';
	} else {
		echo '<p>No elements have been accessed yet.</p>';
	}
	?>
</div>
<div class="col_oneoftwo lastcol">
	<h2>Elements In Progress</h2>
	<ul>
	<li>Tweet for download</li>
	<li>FB like for download</li>
	<li>Download for current social followers</li>
	<li>Email capture</li>
	<li>Download for current mailing list participants</li>
	<li>Download codes</li>
	<li>Paid downloads</li>
	<li>Audio stream</li>
	<li>Secure audio stream</li>
	</ul>
</div>
