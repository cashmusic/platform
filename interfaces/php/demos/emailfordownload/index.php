<?php include('../../../../framework/php/cashmusic.php'); // Initialize CASH Music ?>
<!DOCTYPE html>
<html>
<head>
<title>Email For Download / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/demo.css" rel="stylesheet" type="text/css" />
<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" /> 
</head>

<body>

<div id="mainspc">
	<img src="assets/images/portugaltheman.png" width="325" height="63" alt="Portugal. The Man: Email List" />
	<p>
		Join our email list to get a free download of "When The War Ends" from our record <a href="http://store.portugaltheman.com/american-ghetto/"><i>American Ghetto</i></a>.
	</p>

	<?php CASHSystem::embedElement(100); // CASH element (Portugal email for download / emailcollection) ?>
</div>

<?php include('../../../../framework/php/settings/debug/cashmusic_debug.php'); // Debug ?>
</body>
</html>