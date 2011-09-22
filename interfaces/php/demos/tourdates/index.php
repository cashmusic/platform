<?php include('../../../../framework/php/cashmusic.php'); // Initialize CASH Music ?>
<!DOCTYPE html>
<head> 
<title>Tour Dates / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/demo.css" rel="stylesheet" type="text/css" />
<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />

</head> 
<body>
<div id="wrap"> 

	<div id="mainspc"> 

			<?php cash_embedElement(102); // CASH element (Wild Flag Test / tourdates) ?>

	</div> 

</div> 

<div id="msgspc"></div>

<?php include('../../../../framework/php/settings/debug/cashmusic_debug.php'); // Debug ?>
</body> 
</html>