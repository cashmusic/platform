<p>
Use this page to change primary system settings without editing the cashmusic.ini.php file. 
Advanced settings have been hidden and should not be changed unless you like broken things.	
</p><br />
<div class="col_oneoftwo">
	<h3>Connections</h3>
	<a href="./connections/">Manage connections to third-party services.</a>
	<div class="row_seperator">.</div><br />
	<h3>Database</h3>
	<?php
	if ($migrate_message) {
		echo '<p><span class="highlightcopy">' . $migrate_message . '</span></p>';
	}
	?>
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
		if ($db_type == 'MySQL') { 
			echo ' &nbsp; <span class="altcopystyle fadedtext">(SQLite export coming soon)</span>'; 
		} else {
			echo '<br /><br /><b>Migrate to MySQL</b><p class="altcopystyle fadedtext">Migrating means things will be a lot more robust, and probably quicker too.</p>';
			?>
			<form method="post" action="">
				<input type="hidden" name="domigrate" value="makeitso" />
				<input type="hidden" name="driver" value="mysql" />
				<label for="hostname">Server hostname</label><br />
				<input type="text" id="hostname" name="hostname" />
				<span class="altcopystyle fadedtext">server or server:port</span></span><br />
				<label for="databasename">Database name</label><br />
				<input type="text" id="databasename" name="databasename" />
				<label for="adminuser">Admin User</label><br />
				<input type="text" id="adminuser" name="adminuser" />
				<label for="adminpassword">Admin Password</label><br />
				<input type="password" id="adminpassword" name="adminpassword" />
				<div class="row_seperator">.</div><br />
				<input class="button" type="submit" value="Migrate" />
			</form>
			<?php
		}
	?>
</div>
<div class="col_oneoftwo lastcol">
	<h3>Miscellaneous</h3>
	<?php
	if ($misc_message) {
		echo '<p><span class="highlightcopy">' . $misc_message . '</span></p>';
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