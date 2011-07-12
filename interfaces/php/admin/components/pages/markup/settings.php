<div class="col_onehalf">
	<h3>Add a new service connection:</h3>
	<?php
	$iterate_count = 1;
	foreach ($settings_types_data as $key => $data) {
		$class_string = 'col_onehalf';
		if ($iterate_count % 2 == 0) {
			$class_string = 'col_onehalf lastcol';
		}
		echo '<div class="' . $class_string . '">';
		if (file_exists(ADMIN_BASE_PATH.'/assets/images/settings/' . $key . '.png')) {
			echo '<img src="' . ADMIN_WWW_BASE_PATH . '/assets/images/settings/' . $key . '.png" width="100%" alt="' . $data->name . '" /><br />';
		}
		echo '<small>' . $data->name . '</small><br />';
		echo '</div>';
		$iterate_count++;
	}
	?>
</div>
<div class="col_onehalf lastcol">
	<h3>Current connections:</h3>
	<code>
		<?php
			print_r($settings_types_data);
		?>
	</code>
</div>