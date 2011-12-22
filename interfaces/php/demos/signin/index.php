<?php include('../../../../framework/php/cashmusic.php'); // Initialize CASH Music ?>
<!DOCTYPE html>
<html>
<head>
<title>Sign-In / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/demo.css" rel="stylesheet" type="text/css" />
<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" /> 
</head>

<body>

<div id="mainspc">
	<img src="assets/images/funzone.png" width="250" height="120" id="titleimg" />
	<?php CASHSystem::embedElement(106); // CASH element (Dandy's e-card test / ecard) ?>
		SECURE
	<?php CASHSystem::embedElement(106); // CASH element (Dandy's e-card test / ecard) ?>
</div>

<?php include('../../../../framework/php/settings/debug/cashmusic_debug.php'); // Debug ?>
</body>
</html>