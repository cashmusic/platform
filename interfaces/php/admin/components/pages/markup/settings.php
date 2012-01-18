<p>
Use this page to change primary system settings without editing the cashmusic.ini.php file. 
Advanced settings have been hidden and should not be changed unless you like broken things.	
</p><br />
<div class="col_oneoftwo">
	<h3>Database</h3>
	<label>Current database format</label><br />
	<?php 
		$db_types = array(
			'mysql' => 'MySQL',
			'sqlite' => 'SQLite'
		);
		$db_type = 'unknown';
		if (array_key_exists($platform_settings['driver'],$db_types)) {
			$db_type = $db_types[$platform_settings['driver']];
		}
		echo $db_type;
	?>
	 &nbsp; [ migrate database ]
	<div class="row_seperator">.</div><br />
	<h3>Connections</h3>
	<a href="./connections/">Manage connections to third-party services.</a>
</div>
<div class="col_oneoftwo lastcol">
	<h3>Miscellaneous</h3>
	<?php
	if ($misc_message) {
		echo '<p class="highlightcopy">' . $misc_message . '</p>';
	}
	?>
	<form method="post" action="">
		<input type="hidden" name="domisc" value="makeitso" />
		<label for="systememail">Default system email address</label><br />
		<input type="text" id="systememail" name="systememail" value="<?php echo $platform_settings['systememail']; ?>" />
		<span class="altcopystyle fadedtext">(name@domain.com or "<span class="nobr">Name &lt;name@domain.com&gt;</span>")</span>
		<div class="row_seperator">.</div>
		<label for="timezone">System time zone</label><br />
		<select id="timezone" name="timezone">
			<?php echo AdminHelper::drawTimeZones($platform_settings['timezone']); ?>
		</select>
		<div class="row_seperator">.</div><br />
		<input class="button" type="submit" value="Change settings" />
	</form>	
</div>
<div class="row_seperator">.</div>