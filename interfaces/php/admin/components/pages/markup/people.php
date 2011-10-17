<div class="col_oneoftwo">
	<h2>Lists</h2>
	<?php
	if (!is_array($cash_admin->getStoredData('alllists'))) {
		echo "No lists were found. Sorry.";
	} else {
		foreach ($cash_admin->getStoredData('alllists') as $list) {
			?>
			<div class="callout">
				<h4><?php echo $list['name']; ?></h4>
				<?php echo $list['description']; ?><br />
				<span class="smalltext fadedtext nobr">Created: <?php echo date('M jS, Y',$list['creation_date']); if ($list['modification_date']) { echo ' (Modified: ' . date('F jS, Y',$list['modification_date']) . ')'; } ?></span>
				<div class="tar">
					<br />
					<a href="<?php echo ADMIN_WWW_BASE_PATH . '/people/mailinglists/view/' . $list['id']; ?>" class="mininav_spaced">View</a> <a href="<?php echo ADMIN_WWW_BASE_PATH . '/people/mailinglists/view/' . $list['id']; ?>" class="mininav_spaced">Edit</a> <a href="<?php echo ADMIN_WWW_BASE_PATH . '/people/mailinglists/view/' . $list['id']; ?>" class="mininav_spaced">Export</a> <a href="<?php echo ADMIN_WWW_BASE_PATH . '/people/mailinglists/view/' . $list['id']; ?>" class="mininav_spaced">Delete</a>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>
<div class="col_oneoftwo lastcol">
	<h2>Social</h2>
	<p>
		Twitter and Facebook integrations not set up.
	</p>
</div>