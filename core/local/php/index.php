<?php
	include('Seed.php');

	if (isset($_GET['down'])) {
		$test = new SeedRequest(array('seed_request_type' => 'asset', 'seed_action' => 'redirect','asset_id' => 1));
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>CASH Music : Seed Test</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
Test: <a href="?down=xx">download</a>

<div id="debug" style="font-size:0.85em;margin-top:60px;">
	<b>debug $seed_request->response:</b><br />
	<pre><?php print_r($seed_request->response); ?></pre>

	<br /><br />
	<b>debug $_SESSION:</b><br />
	<pre><?php print_r($_SESSION); ?></pre>
</div>

</body>
</html>