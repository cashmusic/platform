<?php include('../../../../framework/php/cashmusic.php'); // Initialize CASH Music ?>
<!DOCTYPE html>
<head> 
<title>Email Contest Entry / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/demo.css" rel="stylesheet" type="text/css" />
<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />

<link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>

<script src="http://ajax.googleapis.com/ajax/libs/mootools/1.2.4/mootools-yui-compressed.js" type="text/javascript"></script> 
<script src="assets/scripts/fancy.js" type="text/javascript"></script> 

</head> 
<body>
<div id="wrap"> 

	<div id="mainspc"> 
		<img src="assets/images/ironandwine.png" width="820" height="105" alt="Iron &amp; Wine" />
		
		<p id="toptxt">
			Iron &amp; Wine is giving a way a pair of tickets to each of their next three shows. To win just click on any date from the list, enter your email, and click the "sign me up" button. Winners will be notified by email the day before the show.
		</p>
		
		<div id="datesspc">
			<h2>1. Pick a date:</h2>
			<div id="sep08van" class="selectshow">
				<span class="dateloc">Sep 8 . Vancouver, BC</span><br />Vogue
			</div>
			<div id="sep09pdx" class="selectshow">
				<span class="dateloc">Sep 9 . Portland, OR</span><br />MusicFestNW (Pioneer Courthouse Square)
			</div>
			<div id="sep10sea" class="selectshow">
				<span class="dateloc">Sep 10 . Seattle, WA</span><br />The Paramount Theatre
			</div>
		</div>
		
		<div id="contentspc">
			<h2>2. Enter your email to win tickets:</h2>
			<?php CASHSystem::embedElement(101); // CASH element (Iron & Wine ticket contest / emailcollection) ?>

			<br /><br />

			<p id="linksp">
				<a href="http://twitter.com/ironandwine"><img src="assets/images/twitter.png" width="51" height="30" alt="Iron &amp; Wine on Twitter" /></a> &nbsp; <a href="http://facebook.com/ironandwine"><img src="assets/images/facebook.png" width="49" height="30" alt="Iron &amp; Wine on Facebook" /></a><br />
			</p>
		</div>
	</div> 

</div> 

<div id="msgspc"></div>

<?php include('../../../../framework/php/settings/debug/cashmusic_debug.php'); // Debug ?>
</body> 
</html>