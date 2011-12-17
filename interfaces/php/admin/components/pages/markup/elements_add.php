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
			$supported_elements = $page_request->response['payload'];
			$colcount = 1;
			foreach ($elements_data as $element => $data) {
				if (array_search($element, $supported_elements) !== false) {
					if ($colcount % 3 == 0) {
						$secondclass = ' lastcol';
					} else {
						$secondclass = '';
					}
					?>
					<div class="col_oneofthree<?php echo $secondclass; ?>">
						<?php 
						$element_img = CASH_PLATFORM_ROOT.'/elements/'.$element.'/image.jpg';
						if (file_exists($element_img)) {
						?>
						<a href="<?php echo $element; ?>"><img src="data:image/jpg;base64,<?php echo base64_encode(file_get_contents($element_img)); ?>" width="100%" alt="<?php echo $data->name; ?>" /></a><br />
						<?php
						}
						?>
						<h3><?php echo $data->name; ?></h3>
						<small>by <a href="<?php echo $data->url; ?>"><?php echo $data->author; ?></a></small>
						<p><?php echo $data->description; ?></p>
						<a href="<?php echo $element; ?>" class="mockbutton">Add this now</a>
						<br /><br /><br />
					</div>
					<?php
					if ($colcount % 3 == 0) {
						echo "\n" . '<div class="clearfix">.</div>' . "\n";
					}
					$colcount++;
				}
			}
		} else {
			$page_error = "Could not get all needed information.";
		}
	}
	
	if ($page_error) {
		echo '<h3>Error</h3><p>' . $page_error . '</p>';
	}
?>