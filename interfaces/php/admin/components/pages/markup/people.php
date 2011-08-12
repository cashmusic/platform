<div class="col_onehalf">
	<h2>Mailing Lists</h2>
	<?php
	if (!is_array($page_data['lists'])) {
		echo "No lists were found. Sorry.";
	} else {
		foreach ($page_data['lists'] as $list) {
			?>
			<div class="callout">
				<h4><?php echo $list['name']; ?></h4>
				<?php echo $list['description']; ?><br />
				<span class="smalltext fadedtext nobr">Created: <?php echo date('M jS, Y',$list['creation_date']); if ($list['modification_date']) { echo ' (Modified: ' . date('F jS, Y',$list['modification_date']) . ')'; } ?></span>
				<div class="tar">
					<br />
					<a href="<?php echo ADMIN_WWW_BASE_PATH . '/people/mailinglists/view/' . $list['id']; ?>" class="mininav">View List</a>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>
<div class="col_onehalf lastcol">
	<h2>Social</h2>
	<p>
		Twitter and Facebook integrations not set up.
	</p>
	
	<h2>Locked Elements by List</h2>
	<p>
		There are no locked elements.
	</p>
</div>