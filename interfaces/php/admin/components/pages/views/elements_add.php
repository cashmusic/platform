<?php
	$page_error = false;
	if (isset($element_addtype) && $page_request->response) {
		$supported_elements = $page_request->response['payload'];
		if (array_search($element_addtype, $supported_elements) !== false) {
			if (@file_exists(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_addtype . '/add.php')) {
				include(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_addtype . '/add.php');
			} else {
				$page_error = "Could not find the add.php file for this .";
			}
		} else {
			$page_error = "You're trying to add an unsupported element. That's lame.";
		}
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