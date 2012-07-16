<?php include('../../../../framework/php/cashmusic.php'); // Initialize CASH Music ?>
<!DOCTYPE html>
<head> 
<title>Sandbox / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/demo.css" rel="stylesheet" type="text/css" />
<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />
</head> 
<body>

	<div id="mainspc">
		<?php CASHSystem::embedElement(5); ?>
	</div>

<?php include('../../../../framework/php/settings/debug/cashmusic_debug.php'); // Debug ?>
</body> 
</html>