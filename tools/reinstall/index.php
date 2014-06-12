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
			<li><a href="/"><i class="icon icon-certificate"></i>Home</a></li>
			<li><a href="/interfaces/php/admin/"><i class="icon icon-pencil"></i>Login</a></li>
			<li><a href="/docs/"><i class="icon icon-book"></i>Docs</a></li>
			<li><a href="/tools/"><i class="icon icon-cog"></i>Tools</a></li>
		</ul>
</div>

	<div id="mainspc" class="row reinstall">
	
		<h1>Reinstall</h1>

		<h2>
			This will give you a clean install of the platform, no data or usage present, and reset the main user to dev@cashmusic.org with the "dev" password.
		</h2>

		<div style="min-height:350px;">
		

		<?php
			if (isset($_POST['noreallygo'])) {
				$output = shell_exec('rm /vagrant/framework/db/cashmusic_vagrant.sqlite && php /vagrant/.vagrant_settings/vagrant_cashmusic_installer.php');
				/* 
				Unset cookies
				If we don't do this the old session cookie gets all out of sync and sessions get borked.
				*/
				if (isset($_SERVER['HTTP_COOKIE'])) {
					$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
					foreach($cookies as $cookie) {
						$parts = explode('=', $cookie);
						$name = trim($parts[0]);
						setcookie($name, '', time()-1000);
						setcookie($name, '', time()-1000, '/');
					}
				}
				?>
					<h1>Success. The platform has been reinstalled.</h1>
				<?php
			} else {
		?>
				<form method="post" action="">
					<input type="hidden" name="noreallygo" value="1" />
					<button type="submit"><i class='icon icon-cogs'></i>reinstall the platform</button>
				</form>
		<?php 
			}
		?>
		</div>

		<br /><br /><br />
	</div>

	<div id="bottomspc"></div>
</body> 
</html>