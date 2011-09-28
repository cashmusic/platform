<div class="col_onethird">
	<h2>Welcome!</h2>
	<p class="longercopy">
	This is the default landing page. We'll keep this corner for news, updates, and system information.
	</p><p class="longercopy"> 
	The other sections of the page will focus on high-level data showing element usage, traffic, and
	relevant statistics. Imagine it looks really fancy.
	</p>
</div><div class="col_twothirds lastcol usecolor1">
	<h2 class="usecolor1">Elements</h2>
	<div class="col_onethird" style="font-weight:bold;">
		At a Glance<br />
		<span class="majorcallout bgcolor1"><?php echo $page_data['element_active_count']; ?></span> <a href="<?php echo ADMIN_WWW_BASE_PATH . '/elements/' ?>"><big>Active <br />Element<?php if($page_data['element_active_count'] != 1) { echo 's'; } ?></big></a>
		<div class="clearfix">.</div>
		<span class="majorcallout bgcolor0"><?php echo $page_data['element_inactive_count']; ?></span> <a href="<?php echo ADMIN_WWW_BASE_PATH . '/elements/view/' ?>" class="usecolor0"><big>Inactive <br />Element<?php if($page_data['element_inactive_count'] != 1) { echo 's'; } ?></big></a>
	</div>
	<div class="col_onethird">
		<b>Most Active</b>
		<?php
		if (is_array($page_data['element_mostactive'])) {
			$loopcount = 1;
			echo '<ol class="fadedtext">';
			foreach ($page_data['element_mostactive'] as $element) {
				echo '<li><a href="' . ADMIN_WWW_BASE_PATH . '/elements/view/' . $element['id'] . '">' . $element['name'] . '</a> <span class="smalltext nobr">(accessed: ' . $element['count'] . ')</span></li>';
				$loopcount = $loopcount + 1;
				if ($loopcount == 3) { break; }
			}
			echo '</ol>';
		} else {
			echo '<p>No elements have been accessed yet.</p>';
		}
		?>
	</div>
	<div class="col_onethird lastcol">
		<b>Recently Added</b>
		<?php
		if (is_array($page_data['element_recentlyadded'])) {
			$loopcount = 1;
			echo '<ul class="nobullets fadedtext">';
			foreach ($page_data['element_recentlyadded'] as $element) {
				echo '<li><a href="' . ADMIN_WWW_BASE_PATH . '/elements/view/' . $element['id'] . '">' . $element['name'] . '</a></li>';
				if ($loopcount == 3) { break; }
				$loopcount = $loopcount + 1;
			}
			echo '</ul>';
		} else {
			echo '<p>No elements have been accessed yet.</p>';
		}
		?>
	</div>
</div>
	
<div class="clearfix tall">.</div>

<div class="col_onefourth usecolor2">
	<h2 class="usecolor2">Assets</h2>
	<b>Most Accessed</b>
		<?php
		if (is_array($page_data['asset_mostaccessed'])) {
			$loopcount = 1;
			echo '<ol class="fadedtext">';
			foreach ($page_data['asset_mostaccessed'] as $asset) {
				echo '<li><a href="#">' . $asset['title'] . '</a> <span class="smalltext nobr">(accessed: ' . $asset['count'] . ')</span></li>';
				$loopcount = $loopcount + 1;
				if ($loopcount == 3) { break; }
			}
			echo '</ol>';
		} else {
			echo '<p>No assets have been accessed yet.</p>';
		}
		?>
	<b>Recently Added</b>
	<?php
	if (is_array($page_data['asset_recentlyadded'])) {
		$loopcount = 1;
		echo '<ul class="nobullets fadedtext">';
		foreach ($page_data['asset_recentlyadded'] as $asset) {
			echo '<li><a href="#">' . $asset['title'] . '</a></li>';
			if ($loopcount == 3) { break; }
			$loopcount = $loopcount + 1;
		}
		echo '</ul>';
	} else {
		echo '<p>No assets have been added yet.</p>';
	}
	?>
</div><div class="col_onefourth usecolor3">
	<h2 class="usecolor3">People</h2>
	<p>
		<b>List Name</b><br />
		<span class="fadedtext">
			Total members: 4321<br />
			Last 7 days: 123<br />
		</span>
		<a href="#">view details</a>
	</p><p>
		<b>List Name</b><br />
		<span class="fadedtext">
			Total members: 4321<br />
			Last 7 days: 123<br />
		</span>
		<a href="#">view details</a>
	</p>
</div><div class="col_onefourth usecolor4">
	<h2 class="usecolor4">Commerce</h2>
	<p>
		<b>There are 13 outstanding orders that require fulfillment</b><br />
		<a href="#">view outstanding orders</a>
	</p><p>
		<b>Activity This Week</b><br />
		<span class="fadedtext">
			$278.65 gross revenue<br />
			29 orders<br />
			7 different items<br />
		</span>
		<a href="#">view details</a>
	</p>
</div><div class="col_onefourth lastcol usecolor5">
	<h2 class="usecolor5">Calendar</h2>
	<p class="fadedtext">There are no upcoming shows or events.</p>
</div>
