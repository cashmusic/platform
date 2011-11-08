<?php include('../../../../framework/php/cashmusic.php'); // Initialize CASH Music ?>
<!DOCTYPE html>
<head> 
<title>Tour Dates / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width" />
<link href="assets/css/demo.css" rel="stylesheet" type="text/css" />

<!--[if !IE]>-->
<link href="assets/css/demo_mobile.css" type="text/css" rel="stylesheet" media="only screen and (max-device-width: 480px)" />
<link href="assets/css/demo_mobile.css" type="text/css" rel="stylesheet" media="only screen and (-webkit-min-device-pixel-ratio: 2)" />
<!--<![endif]-->

<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />

</head> 
<body>

	<div id="wrap">
		<div id="contentspc">
			<img src="assets/images/wildflag.png" width="420" height="61" alt="Wild Flag" /><br />

			<a href="http://twitter.com/wildflagmusic">Wild Flag on Twitter</a><br />

			<a href="http://www.facebook.com/WILDFLAG">Wild Flag on Facebook</a><br /><br />

			<a href="http://www.youtube.com/watch?v=8J8n9R8rnB8">Watch the video for “Romance”</a><br />

			<a href="http://mergerecords.com/store/store_detail.php?catalog_id=751">Buy the record</a><br />
		</div>

		<div id="datesspc">
			<?php CASHSystem::embedElement(102); // CASH element (Wild Flag Test / tourdates) ?>
			
			<br /><br />
		</div>
	</div>

<?php include('../../../../framework/php/settings/debug/cashmusic_debug.php'); // Debug ?>
</body> 
</html>