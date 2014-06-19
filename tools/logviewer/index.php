<!DOCTYPE html>
<html lang="en">
<head> 
<title>CASH Music | Platform</title>
<meta name="description" content="CASH Music is a nonprofit that empowers musicians through free/open technology and learning." />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width" />

<link rel="icon" href="/docs/assets/images/favicon.ico" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="/docs/assets/css/foundation.min.css" />
<link href='//fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css"  href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" />
<link rel="stylesheet" type="text/css" href="/docs/assets/css/app.css" />

<script type="text/javascript" src="http://js.cashmusic.org/cashmusic.js" data-options="lightboxvideo"></script>

</head> 
<body>

<div id="nav">
	<h1 id="logo"><a href="/"><img src="/docs/assets/images/invert_logo.png" alt="CASH Music"></a></h1>

		<ul>
			<li><a href="/tools/"><i class="icon icon-cog"></i><span>Tools</span></a></li>
			<li><a href="/docs/"><i class="icon icon-book"></i><span>Docs</span></a></li>
			<li><a href="/interfaces/php/admin/"><i class="icon icon-pencil"></i><span>Login</span></a></li>
			<li><a href="/"><i class="icon icon-certificate"></i><span>Home</span></a></li>
		</ul>
</div>

	<div id="mainspc" class="log">

		<div class="row">
			<div class="twelve columns">
			
		<h1>/var/log/apache2/error.log</h1>

				<div id="logviewer">
					<?php
					$output = shell_exec('tail -n 100 /var/log/apache2/error.log');
					if (empty($output)) {
						$output = 'The error log is empty. Nice work.';
					}
					echo "<pre>$output</pre>";
					?>
				</div>

			</div>
		</div>

		<br /><br /><br />
	</div>

	<div id="bottomspc"></div>
</body> 
</html>