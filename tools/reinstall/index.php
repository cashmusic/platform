<!DOCTYPE html>
<html lang="en">
<head> 
<title>CASH Music / Reinstall</title>
<meta name="description" content="CASH Music is a nonprofit that empowers musicians through free/open technology and learning." />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width" />

<link rel="icon" type="image/png" href="http://2ea7029ee5fd6986c0a6-6d885a724441c07ff9b675222419a9d2.r58.cf2.rackcdn.com/ui/01/images/badge.png" />
<link rel="stylesheet" type="text/css" href="/docs/assets/css/foundation.min.css" />
<link rel="stylesheet" type="text/css" href='https://fonts.googleapis.com/css?family=Amatic+SC:400,700|Roboto+Condensed:400,700italic,400italic,700,300,300italic|Noto+Serif:400,700italic,400italic,700' />
<link rel="stylesheet" type="text/css"  href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" />
<link rel="stylesheet" type="text/css" href="/docs/assets/css/app.css" />

</head> 
<body>

	<div id="topspc"></div>
	<div id="mainspc" class="row">
		<a href="/"><img id="homeseal" src="/docs/assets/images/badge.png" width="76" height="76" alt="home" /></a>
		<div id="nav">
			<a href="/"><i class="icon icon-pencil"></i>Home</a>
			<a href="/docs/"><i class="icon icon-book"></i>Docs</a>
			<a href="/tools/"><i class="icon icon-cog"></i>Tools</a>
		</div>

		<h3>Reinstall</h3><br />

		<p>
			This will give you a clean install of the platform, no data or usage present, and reset the main user to dev@cashmusic.org with the "dev" password.
		</p>

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