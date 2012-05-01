<?php include('../../../../framework/php/cashmusic.php'); // Initialize CASH Music ?>
<!DOCTYPE html>
<html>
<head>
<title>Sign-In / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/demo.css" rel="stylesheet" type="text/css" />
<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" /> 
<link href='http://fonts.googleapis.com/css?family=Arimo:700' rel='stylesheet' type='text/css'>
</head>

<body>

<div id="mainspc">
	<img src="assets/images/funzone.png" width="160" height="77" id="titleimg" />
	<span id="navtitle">&nbsp;MGMT<b>FUNZONE</b></span>
	<div id="navmenu">
		<a href="#">NEWS</a><a href="#">EVENTS</a><a href="#" class="selected">TICKETS</a><a href="#">MERCH</a><a href="#">PHOTOS</a><a href="#">MUSIC+VIDEO</a><a href="#">SHOW ARCHIVE</a>
	</div>
	<?php CASHSystem::embedElement(106); // CASH element (Dandy's e-card test / ecard) ?>
		It's working. Here's a sample element:<br />
		<?php CASHSystem::embedElement(107); // CASH element (Front page news box) ?> 
	<?php CASHSystem::embedElement(106); // CASH element (Dandy's e-card test / ecard) ?>
</div>

<?php include('../../../../framework/php/settings/debug/cashmusic_debug.php'); // Debug ?>
</body>
</html>