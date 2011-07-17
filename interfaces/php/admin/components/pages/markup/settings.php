<div class="col_onehalf">
	<h3>Add a new service connection:</h3>
	<p>
		Easily connect to any of these services:
	</p>
	<?php
	$colcount = 1;
	foreach ($settings_types_data as $key => $data) {
		$class_string = 'col_onehalf';
		if ($colcount % 2 == 0) {
			$class_string = 'col_onehalf lastcol';
		}
		echo '<div class="' . $class_string . '">';
		echo '<a href="' . ADMIN_WWW_BASE_PATH . '/settings/add/' . $key . '">';
		if (file_exists(ADMIN_BASE_PATH.'/assets/images/settings/' . $key . '.png')) {
			echo '<img src="' . ADMIN_WWW_BASE_PATH . '/assets/images/settings/' . $key . '.png" width="100%" alt="' . $data->name . '" /><br />';
		}
		echo '<small>' . $data->name . '</small></a><br />';
		echo '</div>';
		if ($colcount % 2 == 0) {
			echo '<div class="row_seperator">.</div>';
		}
		$colcount++;
	}
	?>
</div>
<div class="col_onehalf lastcol">
	<?php
		if (isset($settings_action)) {
			if ($settings_action == 'add') {
				if (!isset($_POST['dosettingsadd'])) {
					if (array_key_exists($settings_type, $settings_types_data)) {
						echo '<h3>Connect to ' . $settings_types_data[$settings_type]->name . '</h3><p>' . $settings_types_data[$settings_type]->description . '</p>';
						?>
						<form method="post" action="">
							<input type="hidden" name="dosettingsadd" value="makeitso" />
							<input type="hidden" name="settings_type" value="<?php echo $settings_type; ?>" />
							<label for="settings_name">Name</label><br />
							<input type="text" id="settings_name" name="settings_name" placeholder="Give It A Name" />
					
							<div class="row_seperator tall">.</div>
					
							<?php
							foreach ((array) $settings_types_data[$settings_type]->dataTypes as $key => $data) {
								echo '<label for="settings_' . $key . '">' . $key . '</label><br />';
								echo '<input type="text" id="settings_' . $key . '" name="settings_' . $key . '" placeholder="' . ucfirst($key) . '" />';
								echo '<div class="row_seperator">.</div>';
							}
							?>
							<div class="row_seperator">.</div><br />
							<div class="tar">
								<input class="button" type="submit" value="Add The Connection" />
							</div>
						</form>
						<?php
					} else {
						echo '<h3>Error</h3><p>The requested setting type could not be found.</p>';
					}
				} else {
					$settings_data_array = array();
					foreach ((array) $settings_types_data[$settings_type]->dataTypes as $key => $data) {
						$settings_data_array['settings_' . $key] = $_POST['settings_' . $key];
					}
					$result = $page_data_object->addSettings(
						$_POST['settings_name'],
						$_POST['settings_type'],
						$settings_data_array
					);
					if ($result) {
						?>
						<h3>Success</h3>
						<p>
							Everything was added successfully. You'll see the new connection added to the 
							<a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/settings/">main settings list</a>.
						</p>
						<?php
					} else {
						?>
						<h3>Error</h3>
						<p>
							Something went wrong. Please make sure you're using a unique name for this
 							setting. Not only is that just smart, it's required. 
							<a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/settings/">Try again.</a>
						</p>
						<?php
					}
				}
			}
		} else {
			echo '<h3>Current connections:</h3><p>Here are the settings that have already been added:</p>';
			foreach ($settings_for_user as $key => $data) {
			?>
				<div class="callout">
					<h4><?php echo $data['name']; ?></h4>
					<?php
					if (array_key_exists($data['type'],$settings_types_data)) {
						echo '<b>' . $settings_types_data[$data['type']]->name . '</b> ';
					} else {
						//echo '<b>' . $data['type'] . '</b> ';
					}
					?>
					&nbsp; <span class="smalltext fadedtext">Created: <?php echo date('M jS, Y',$data['creation_date']); if ($data['modification_date']) { echo ' (Modified: ' . date('F jS, Y',$data['modification_date']) . ')'; } ?></span>
					<div class="tar">
						<a href="./delete/<?php echo $data['id']; ?>/" class="needsconfirmation mininav">Delete</a>
					</div>
				</div>
			<?php
			}
		}
	?>
</div>