<!DOCTYPE html>
<head> 
<title>Sandbox / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/demo.css" rel="stylesheet" type="text/css" />
<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />
</head> 
<body>

	<div id="mainspc">
		<?php include_once('/Users/jessevondoom/Dropbox/Code/cashmusic-github/platform/framework/php/cashmusic.php');CASHSystem::embedElement(1); // (A record sale) ?>
		<br /><br />

		<script type="text/javascript" src="http://localhost:8888/interfaces/php/public/cashmusic.js"></script>
		<script type="text/javascript">window.cashmusic.embed('http://localhost:8888/interfaces/php/public','3');</script>
	</div>

<?php include('../../../../framework/php/settings/debug/cashmusic_debug.php'); // Debug ?>
</body> 
</html>