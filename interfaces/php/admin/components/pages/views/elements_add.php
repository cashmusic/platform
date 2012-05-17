<?php
	$page_error = false;
	if (isset($element_addtype) && $page_request->response) {
		if (isset($cash_admin->page_data['error_message'])) {
			echo '<p><span class="highlightcopy">' . $cash_admin->page_data['error_message'] . '</span></p>';
		}
		echo $element_rendered_content;
	} else {
		if (isset($page_request->response) && $elements_data) {
			?>
			<div class="col_oneofthree">
				<?php
				foreach ($elements_sorted['col1'] as $element => $data) {
					drawFeaturedElement($element,$data);
				}
				?>
			</div>
			<div class="col_oneofthree">
				<?php
				foreach ($elements_sorted['col2'] as $element => $data) {
					drawFeaturedElement($element,$data);
				}
				?>
			</div>
			<div class="col_oneofthree lastcol">
				<?php
				foreach ($elements_sorted['col3'] as $element => $data) {
					drawFeaturedElement($element,$data);
				}
				?>
			</div>
			<div class="clearfix">.</div>
			<?php
		} else {
			$page_error = "Could not get all needed information.";
		}
	}
	
	if ($page_error) {
		echo '<h3>Error</h3><p>' . $page_error . '</p>';
	}
?>