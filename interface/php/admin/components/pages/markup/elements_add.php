<?php
	if (isset($element_addtype) && $page_request->response) {
		$supported_elements = $page_request->response['payload'];
		if (array_search($element_addtype, $supported_elements) !== false) {
			if (@file_exists(ADMIN_BASE_PATH.'/components/elements' . '/' . $element_addtype . '/add.php')) {
				include(ADMIN_BASE_PATH.'/components/elements' . '/' . $element_addtype . '/add.php');
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
					<div class="col_onethird<?php echo $secondclass; ?>">
						<div class="element_image" style="background-image:url(<?php echo ADMIN_WWW_BASE_PATH . '/components/elements/' . $element . '/image.png'; ?>);"></div>
						<h3><?php echo $data->name; ?></h3>
						<small>by <a href="<?php echo $data->url; ?>"><?php echo $data->author; ?></a></small>
						<p><?php echo $data->description; ?></p>
						<a href="<?php echo $element; ?>" class="mockbutton">Add this now</a>
					</div>
					<?php
					$colcount++;
				}
			}
		} else {
			$page_error = "Could not get all needed information.";
		}
	}
?>