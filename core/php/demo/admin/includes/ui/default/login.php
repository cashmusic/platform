<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>CASH Music: Admin</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />
	<link href="<?php echo WWW_BASE_PATH; ?>/_assets/css/admin.css" rel="stylesheet" type="text/css" />
	<style type="text/css">
		#mainspc {padding-top:150px;}
		#navmenu {top:0;height:4px;overflow:hidden;padding-top:0;padding-bottom:0;}
		#navmenu .navitem:hover {background-color:#fff;background-image:none;cursor:auto;}
		#pagedisplay {margin:0 auto;}
		#loginspc {margin:0 auto;width:160px;height:220px;padding:70px 40px 50px 100px;background:#ff0 url(<?php echo WWW_BASE_PATH; ?>/_assets/images/bg_login.gif) left top no-repeat;text-align:left;}
		#loginspc h1 {font-size:3.5em;color:#fff;margin-left:-3px;}
		#loginspc h2 {margin-top:20px;}
		#loginspc label {color:#000;}
		#loginspc input {border:1px solid #fff;}
		#loginspc input:active, #loginspc input:focus {outline:0;border:1px solid #000;}
		#loginspc input.button {margin:24px -8px 0 0;background-color:#fff;}
		#loginspc input.button:hover {background-color:#000;color:#fff;border:1px solid #000;}
		#loginspc input.button:active {background-color:#fff;color:#000;}
	</style>

</head>
<body>

<div id="wrap">
	<div id="mainspc">
		<div id="cash_sitelogo"><a href="http://cashmusic.org/"><img src="<?php echo WWW_BASE_PATH; ?>/_assets/images/cash.png" alt="CASH Music" width="30" height="30" /></a></div>
		<div id="navmenu">
			<div class="navitem bgcolor1"></div>
			<div class="navitem bgcolor2"></div>
			<div class="navitem bgcolor3"></div>
			<div class="navitem bgcolor4"></div>
			<div class="navitem bgcolor5"></div>
		</div>

			<div id="loginspc">
				<h1>Seed.</h1>
				<h2>Log In:</h2>
				
				<form method="post" action="./"> 
					<label for="address">email</label>
					<input type="text" name="address" value="" /><br />
					<label for="address">password</label>
					<input type="password" name="password" value="" /><br />
					<input type="hidden" name="login" value="sure" /> 
					<div style="text-align:right;">
					<input type="submit" value="log in" class="button" /><br />
					</div>
				</form>
				
			</div>

	</div>

</div>

<div id="footer">
	<p><b>&copy; 2011 CASH Music.</b> All our code is open-source. <a href="<?php echo WWW_BASE_PATH; ?>/licenses/" style="margin-left:0;">Learn more</a>. <a href="http://help.cashmusic.org/">Get help</a> <a href="http://cashmusic.org/donate" style="color:#999;"><b>Donate</b></a></p>
</div>

</body>
</html>