<?php include('../../../../framework/php/cashmusic.php'); // Initialize CASH Music ?>
<!DOCTYPE html>
<head> 
<title>Filtered Social Feeds / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/demo.css" rel="stylesheet" type="text/css" />

<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />

</head> 
<body>

	<img src="assets/images/title.png" id="titleimg" alt="Amanda and Neil Have a Conversation" />

	<div id="contentspc">
		<?php CASHSystem::embedElement(103); // CASH element (Palmer/Gaiman filtered social feeds / socialfeeds) ?>
	</div>

<?php include('../../../../framework/php/settings/debug/cashmusic_debug.php'); // Debug ?>
</body> 
</html>