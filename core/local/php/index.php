<?php
	include('Seed.php');

	if (isset($_GET['down'])) {
		$test = new SeedRequest(array('seed_action' => 'asset', 'seed_command' => 'redirect','asset_id' => 1));
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
</body>
</html>