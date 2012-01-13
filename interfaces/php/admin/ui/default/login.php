<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Log In</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />
	<link href="<?php echo ADMIN_WWW_BASE_PATH; ?>/ui/default/assets/css/admin.css" rel="stylesheet" type="text/css" />
	<style type="text/css">
		h1.tryagain {color:#ff0 !important;}
		#mainspc {padding-top:150px;}
		#navmenu {top:0;height:6px;overflow:hidden;padding-top:0;padding-bottom:0;}
		#navmenu .navitem:hover {background-image:none;cursor:auto;}
		#pagedisplay {margin:0 auto;}
		#loginspc {position:relative;top:6px;width:960px;height:220px;margin:0 auto;color:#000;}
		#loginbox {position:relative;width:260px;height:220px;padding:35px 430px 105px 40px;background:#437bac url(<?php echo ADMIN_WWW_BASE_PATH; ?>/ui/default/assets/images/bg_login.jpg) left top no-repeat;text-align:left;}
		#loginbox h1 {font-size:88px;color:#fff;margin:6px 0 26px 0;white-space:nowrap;}
		#loginbox label {color:#69d;}
		#loginbox input {border:1px solid #fff;width:244px;padding:8px;}
		#loginbox input:active, #loginspc input:focus {outline:0;border:1px solid #000;}
		#loginbox input.button {margin:18px 0 0 0;background-color:#ff0;color:#000;border:1px solid #ff0;}
		#loginbox input.button:hover {background-color:#000;color:#fff;border:1px solid #000;}
		#loginbox input.button:active {background-color:#fff;color:#000;border:1px solid #fff;}
	</style>

</head>
<body>

<div id="wrap">
	<div id="mainspc">

		<div id="chromespc">
			
			<div id="accountspc"></div>
			<div id="navmenu">
				<div class="navitem bgcolor1"></div>
				<div class="navitem bgcolor2"></div>
				<div class="navitem bgcolor3"></div>
				<div class="navitem bgcolor4"></div>
				<div class="navitem bgcolor5"></div>
			</div>

			<div id="loginspc">
				<div id="loginbox">
					<small><b>CASH Music</b></small>
					<h1<?php if ($login_message == 'Try Again') {echo ' class="tryagain"';} ?>><?php echo $login_message; ?>:</h1>
			
					<form method="post" action=""> 
						<label for="address">email</label>
						<input type="text" name="address" value="" /><br />
						<label for="address">password</label>
						<input type="password" name="password" value="" /><br />
						<input type="hidden" name="login" value="1" /> 
						<div style="text-align:right;">
						<input type="submit" value="log in" class="button" /><br />
						</div>
					</form>
				</div>
			</div>
			
		</div>

	</div>

</div>

<div id="footer">
	<p><b>&copy; 2011 CASH Music.</b> All our code is open-source. <a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/licenses/" style="margin-left:0;">Learn more</a>. <a href="http://help.cashmusic.org/">Get help</a> <a href="http://cashmusic.org/donate" class="donatelink"><b>Donate</b></a></p>
</div>

</body>
</html>