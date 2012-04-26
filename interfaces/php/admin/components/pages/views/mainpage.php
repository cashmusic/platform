<?php
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	echo '<div class="mainpage introductorybanner"><img src="' . ADMIN_WWW_BASE_PATH . '/ui/default/assets/images/bg_mainpagebanner.jpg" class="bannerbg" alt="jackson" /><div class="mainbannercontent">';
	echo '<a href="' . ADMIN_WWW_BASE_PATH . '/assets/" class="usecolor2">Assets</a>, your songs, photos, cover art, etc. <a href="' . ADMIN_WWW_BASE_PATH . '/people/" class="usecolor3">People</a>, fans, mailing lists, anyone you need to connect with on a regular basis. <a href="' . ADMIN_WWW_BASE_PATH . '/commerce/" class="usecolor4">Commerce</a> is where you’ll find info on all your orders. And <a href="' . ADMIN_WWW_BASE_PATH . '/calendar/" class="usecolor5">Calendar</a>, keeps a record of all your shows in one place.<br /><br />';
	echo 'The last main category is <a href="' . ADMIN_WWW_BASE_PATH . '/elements/" class="usecolor1">Elements</a>, where Assets, People, Commerce, and Calendar can be combined to make customized tools for your site. Things like email collection, song players, and social feeds all just a copy/paste away.<br /><br />';
	echo '<div class="moreinfospc">Need more info? Check out the <a href="' . ADMIN_WWW_BASE_PATH . '/help/gettingstarted/" class="helplink">Getting Started</a> page.</div>';
	echo '</div><div class="closelink"><a href="' . ADMIN_WWW_BASE_PATH . '/mainpage?hidebanner=true">close <span class="icon x_alt"></span></a></div></div>';
}
?>

<div class="col_oneofthree">
	<p class="altcopystyle">
	This is the default landing page. We'll keep this corner for news, updates, and system information.
	</p><p class="altcopystyle"> 
	The other sections of the page will focus on high-level data showing element usage, traffic, and
	relevant statistics. Imagine it looks really fancy.
	</p>
</div><div class="col_twoofthree lastcol usecolor1 callout" style="width:61%;">
	<h2 class="usecolor1">Elements</h2>
	<div class="col_oneofthree" style="font-weight:bold;">
		At a Glance<br />
		<span class="majorcallout bgcolor1"><?php echo (int) $cash_admin->getStoredData('element_active_count'); ?></span> <a href="<?php echo ADMIN_WWW_BASE_PATH . '/elements/' ?>" class="usecolor1"><big>Active <br />Element<?php if((int) $cash_admin->getStoredData('element_active_count') != 1) { echo 's'; } ?></big></a>
		<div class="clearfix">.</div>
		<span class="majorcallout bgcolor0"><?php echo (int) $cash_admin->getStoredData('element_inactive_count'); ?></span> <a href="<?php echo ADMIN_WWW_BASE_PATH . '/elements/view/' ?>" class="usecolor0"><big>Inactive <br />Element<?php if((int) $cash_admin->getStoredData('element_inactive_count') != 1) { echo 's'; } ?></big></a>
	</div>

	<div class="col_oneofthree">
		<b>Most Active</b>
		<?php
		$mostaccessed_response = $cash_admin->getStoredResponse('element_mostactive');
		if ((int) $cash_admin->getStoredData('element_active_count')) {
			$loopcount = 1;
			echo '<ol class="fadedtext">';
			foreach ($mostaccessed_response['payload'] as $element) {
				echo '<li><a href="' . ADMIN_WWW_BASE_PATH . '/elements/view/' . $element['id'] . '">' . $element['name'] . '</a> <span class="smalltext nobr">(accessed: ' . $element['count'] . ')</span></li>';
				$loopcount = $loopcount + 1;
				if ($loopcount == 3) { break; }
			}
			echo '</ol>';
		} else {
			echo '<p class="fadedtext">No elements have been accessed.</p>';
		}
		?>
	</div>
	<div class="col_oneofthree lastcol">
		<b>Recently Added</b>
		<?php
		if (is_array($cash_admin->getStoredResponse('element_recentlyadded',true))) {
			$loopcount = 1;
			echo '<ul class="nobullets fadedtext">';
			foreach ($cash_admin->getStoredResponse('element_recentlyadded',true) as $element) {
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

<div class="col_oneoffour usecolor2">
	<h2 class="usecolor2">Assets</h2>
	<b>Most Accessed</b>
		<?php
		$mostaccessed_response = $cash_admin->getStoredResponse('asset_mostaccessed');
		if (is_array($mostaccessed_response['payload'])) {
			$loopcount = 1;
			echo '<ol class="fadedtext">';
			foreach ($mostaccessed_response['payload'] as $asset) {
				echo '<li><a href="./assets/edit/file/' . $asset['id'] . '">' . $asset['title'] . '</a> <span class="smalltext nobr">(accessed: ' . $asset['count'] . ')</span></li>';
				$loopcount = $loopcount + 1;
				if ($loopcount == 3) { break; }
			}
			echo '</ol>';
		} else {
			echo '<p class="fadedtext">No assets have been accessed.</p>';
		}
		?>
	<b>Recently Added</b>
		<?php
		if (is_array($cash_admin->getStoredResponse('asset_recentlyadded',true))) {
			$loopcount = 1;
			echo '<ul class="nobullets fadedtext">';
			foreach ($cash_admin->getStoredResponse('asset_recentlyadded',true) as $asset) {
				echo '<li><a href="./assets/edit/file/' . $asset['id'] . '">' . $asset['title'] . '</a><br /><span class="smalltext fadedtext nobr">created: ' . date('M jS, Y',$asset['creation_date']) . '</span></li>';
				if ($loopcount == 3) { break; }
				$loopcount = $loopcount + 1;
			}
			echo '</ul>';
		} else {
			echo '<p class="fadedtext">No assets have been added yet.</p>';
		}
		?>
</div><div class="col_oneoffour usecolor3">
	<h2 class="usecolor3">People</h2>
	<?php
		if (count($lists_array)) {
			foreach ($lists_array as $list) {
				echo '<p><b>' . $list['name'] . '</b><br />';
				echo '<span class="fadedtext">';
				echo 'Total members: ' . $list['total'] . '<br />';
				echo 'Last 7 days:' . $list['lastweek'] . '<br />';
				echo '</span>';
				echo '<a href="./people/lists/view/' . $list['id'] . '">view details</a></p>';
			}
		} else {
			echo '<p class="fadedtext">There are no lists defined.</p>';
		}
	?>
</div><div class="col_oneoffour usecolor4">
	<h2 class="usecolor4">Commerce</h2>
	<p class="fadedtext">
		Coming soon.
	</p>
	<!--
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
	-->
</div><div class="col_oneoffour lastcol usecolor5">
	<h2 class="usecolor5">Calendar</h2>
	<b>This week</b><br />
	<?php
	$thisweek_response = $cash_admin->getStoredResponse('events_thisweek');
	if ($thisweek_response['status_uid'] == 'calendar_geteventsbetween_200') {
		echo '<ul class="nobullets fadedtext">';
		foreach ($cash_admin->getStoredResponse('events_thisweek',true) as $event) {
			echo '<li><a href="./calendar/events/edit/' . $event['event_id'] . '">' . date('d M',$event['date']) . ': ' . $event['venue_city'] . ', ' . $event['venue_region'] . '</a><br /><span class="smalltext nobr">@ ' . $event['venue_name'] . '</span></li>';
		}
		echo '</ul>';
	} else {
		echo '<p class="fadedtext">There are no upcoming events.</p>';
	}
	?>
</div>