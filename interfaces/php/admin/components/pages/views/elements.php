<?php
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	echo '<div class="introductorybanner"><div class="titlespc"><h2>Elements</h2>manage your <b>contacts</b><br />create and maintain <b>lists</b><br />monitor <b>social</b> media</div>';
	echo 'Combine everything else and build functionality, check analytics for existing elements, and get embed codes to use your elements on your site.';
	echo '<div class="closelink"><a href="?hidebanner=true">close <span class="icon x_alt"></span></a></div></div>';
}
?>

<div class="col_oneoftwo">
	<h2>Most Active Elements</h2>
	<?php
	if ($page_data['element_mostactive']) {
		$loopcount = 1;
		echo '<ol class="fadedtext">';
		foreach ($page_data['element_mostactive'] as $element) {
			echo '<li><a href="' . ADMIN_WWW_BASE_PATH . '/elements/view/' . $element['id'] . '">' . $element['name'] . '</a> <span class="smalltext nobr">// accessed: ' . $element['count'] . '</span></li>';
			$loopcount = $loopcount + 1;
			if ($loopcount == 6) { break; }
		}
		echo '</ol>';
	} else {
		echo '<p>No elements have been accessed yet.</p>';
	}
	?>
</div>
<div class="col_oneoftwo lastcol">
	<h2>Recently Added Elements</h2>
	<?php
	if ($page_data['element_recentlyadded']) {
		$loopcount = 1;
		echo '<ul class="alternating linkonly fadedtext">';
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
