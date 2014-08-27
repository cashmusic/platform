<?php
if (isset($_GET['element'])) {
	$element = $_GET['element'];

	require_once('../../constants.php');
	$element_img = str_replace('/cashmusic.php','',CASH_PLATFORM_PATH).'/elements/'.$element.'/image.jpg';

	if (file_exists($element_img)) {
		header("Content-Type: image/jpeg");
		header("Content-Length: " . filesize($element_img));
		readfile($element_img);
		exit;
	} else {
		// spit out an empty/transparent 1x1 PNG
		header("Content-Type: image/png");
		echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAABBJREFUeNpi/v//PwNAgAEACQsDAUdpTjcAAAAASUVORK5CYII=');
	}
}
?>