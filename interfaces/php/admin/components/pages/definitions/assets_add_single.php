<?php
// add unique page settings:
$page_title = 'Assets: Add A Single Asset';
$page_tips = 'Add a single file.';

// parsing posted data:
if (isset($_POST['doassetadd'])) {
	$asset_settings = $_POST['settings_id'];
	$asset_title = $_POST['asset_title'];
	$asset_location = $_POST['asset_location'];
	$asset_description = $_POST['asset_description'];

	$asset_tags = false;
	$asset_metadata = false;

	foreach ($_POST as $key => $value) {
		if (substr($key,0,3) == 'tag' && $value !== '') {
			if (!$asset_tags) {
				$asset_tags = array();
			}
			$asset_tags[] = $value;
		}
		if (substr($key,0,11) == 'metadatakey' && $value !== '') {
			$metadatavalue = $_POST[str_replace('metadatakey','metadatavalue',$key)];
			if ($metadatavalue) {
				if (!$asset_metadata) {
					$asset_metadata = array();
				}
				$asset_metadata[$value] = $metadatavalue;
			}
		}
	}
}
?>