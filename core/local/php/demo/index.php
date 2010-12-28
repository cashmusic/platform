<?php
	include('../Seed.php');

	if (isset($_GET['down'])) {
		$test = new SeedRequest(array('seed_request_type' => 'asset', 'seed_action' => 'redirect','asset_id' => 1));
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>CASH Music : Seed Test</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<style type="text/css">
#mainspc {width:450px;margin:0 auto;padding-top:100px;padding-bottom:30px;font-family:helvetica,arial,sans-serif;}
#debug {font-size:0.85em;margin-top:60px;}
h2 {font:2em/1.5em HelveticaNeueLTStd-UltLt,"HelveticaNeueLT Std UltLt","Helvetica Neue Ultra Light","Helvetica Neue",HelveticaNeue-UltraLight,HelveticaLTStd-Light,Helvetica,Arial,sans-serif;font-weight:100;}
h3 {font:1.5em/1.5em HelveticaNeueLTStd-UltLt,"HelveticaNeueLT Std UltLt","Helvetica Neue Ultra Light","Helvetica Neue",HelveticaNeue-UltraLight,HelveticaLTStd-Light,Helvetica,Arial,sans-serif;font-weight:100;margin:0;}
</style>
</head>

<body>

<div id="mainspc">
	<h2>Tests</h2>
	Test: <a href="?down=xx">download</a>

	<div id="debug">
		<h3>debug $seed_request->response:</h3>
		<pre><?php print_r($seed_request->response); ?></pre>

		<br /><br />
		<h3>debug $_SESSION:</h3>
		<pre><?php print_r($_SESSION); ?></pre>
	</div>
</div>

</body>
</html>