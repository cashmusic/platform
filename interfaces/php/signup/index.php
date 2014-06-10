<?php 
session_start();
// include the necessary bits, define the page directory
// Define constants too
$root = dirname(__FILE__);
$cash_settings = json_decode(getenv('cashmusic_platform_settings'),true);
if ($cash_settings) {
	if (isset($cash_settings['platforminitlocation'])) {
		// this one isn't set for single-instance installs, generally. so we use the physical path above
		$cashmusic_root = $_SERVER['DOCUMENT_ROOT'] . $cash_settings['platforminitlocation'];
	}	
}
// launch CASH Music
require_once($cashmusic_root);

$system_message = '';
$success = false;

if (isset($_POST['douseradd'])) {
	if (!empty($_POST['address']) && isset($_POST['termsread'])) {	
		if(filter_var($_POST['address'], FILTER_VALIDATE_EMAIL)) {
			$username_success = false;
			$final_username = strtolower(preg_replace("/[^a-z0-9]+/i", '',$_POST['username']));
			if (!empty($final_username)) {
				$username_request = new CASHRequest(
					array(
						'cash_request_type' => 'people', 
						'cash_action' => 'getuseridforusername',
						'username' => $final_username
					)
				);
				if ($username_request->response['payload']) {
					$final_username = strtolower(str_replace(array('@','.'),'',$_POST['address']));
				} else {
					$username_success = true;
				}
			} else {
				$final_username = strtolower(str_replace(array('@','.'),'',$_POST['address']));
			}

			$add_request = new CASHRequest(
				array(
					'cash_request_type' => 'system', 
					'cash_action' => 'addlogin',
					'address' => $_POST['address'],
					'password' => md5(time() . 'string' . rand(0,999999)),
					'is_admin' => true,
					'username' => $final_username,
					'data' => array('agreed_terms' => time())
				)
			);
			if ($add_request->response['payload']) {
				CASHSystem::sendEmail(
					'Your CASH Music account is ready',
					false,
					$_POST['address'],
					'Your new CASH Music account has been created successfully. '
						. 'To get started head to http://cashmusic.org/admin/ and reset your password. '
						. 'Just click the "forgot your password" link and enter your email address.'
						. "\n\n"
						. 'Remember this is a beta test — we don\'t say "test" lightly, so it comes with some '
						. 'caveats. We\'ll be sharing details and walkthroughs on our blog at: '
						. 'http://cashmusic.org'
						. "\n\n"
						. 'We\'d love any and all feedback. Comments are open on the blog for a reason, and '
						. 'as always feel free to email or tweet your thoughts.'
						. "\n\n"
						. 'Godspeed!',
					'Welcome to the CASH Music beta'
				);
				if ($username_success) {
					$system_message = '<p>Success. You should see an activation mail with instructions in your inbox soon. Welcome!</p>';
				} else {
					$system_message = '<p>Success. Mostly. The username you requested was already taken. You should see an activation mail with instructions in your inbox soon, and you can choose a new username on the "Your Account" page of the admin. Welcome!</p>';
				}
				$success = true;
			}
		} else {
			$system_message = '<p><span class="highlightcopy">Error. Please enter a valid email address.</span></p>';
		}
	} else {
		$system_message = '<p><span class="highlightcopy">Error. Make sure you have agreed to the terms of service.</span></p>';
	}
}

?>
<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width" />
	<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />

	<title>CASH Music</title>

	<link rel="stylesheet" href="assets/css/foundation.min.css">
	<link rel="stylesheet" href="assets/css/app.css">

	<style type="text/css">
		/* FORMS */
		form span {line-height:2.5em;}
		input,textarea,select {font:italic 14px/1.25em georgia, times, serif !important;border:1px solid #dddddf;width:100%;padding:8px;background-color:#fff;box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;}
		input:active, input:focus, textarea:focus {outline:0;border:1px solid #888;background-color:#fff;}
		input[type="submit"], input[type="button"], a.mockbutton {background-color:#777;padding:8px 18px 8px 18px !important;font:bold 14px/1.25em helvetica,"segoe ui","segoe wp",arial,sans-serif !important;cursor:pointer;width:auto !important;border:1px solid transparent;color:#fff;-webkit-transition: all 0.25s ease-in-out;-moz-transition: all 0.25s ease-in-out;-o-transition: all 0.25s ease-in-out;transition: all 0.25s ease-in-out;}
		input[type="submit"]:hover, input[type="button"]:hover, a.mockbutton:hover {background-color:#000 !important;color:#fff;}
		input[type="checkbox"],input[type="radio"] {width:auto !important;margin-top:8px;}
		label {font-size:11px;text-transform:uppercase;color:#a49c9c;}
		select {height:34px;line-height:34px;width:100%;padding:8px;border:none;background-color:#ededef;background-image:linear-gradient(top, #dfdfdf 0%, #efefef 100%);background-image:-moz-linear-gradient(top, #dfdfdf 0%, #efefef 100%);border-radius:5px;}
		select option {padding:8px;}
		select:active, select:focus {outline:2px solid #ff0;}
		textarea.tall {height:200px;}
		form.inline {display:inline;}
		form.inline input {width:3.25em !important;border:1px solid #fafaf8;}
		form.inline input:active, form.inline input:focus {outline:0;border:1px solid #888;}
		form.inline input.inlinesubmit {font:14px/1.5em helvetica,"helvetica neue","segoe ui","segoe wp",arial,sans-serif !important;background-color:transparent;cursor:pointer;font-weight:bold !important;width:auto !important;padding-left:0 !important;padding-right:0 !important;}

		.highlightcopy {padding:2px 6px 2px 6px;background-color:#ff0;font:italic 12px/1.75em georgia, times, serif;}
	</style>

	<!-- IE Fix for HTML5 Tags -->
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
	<div data-0="bottom:29%;background-color:rgb(255,255,0);" data-410="bottom:10%;background-color:rgb(255,255,0);" data-650="background-color:rgb(255,255,0);" data-700="background-color:rgb(223,8,84);" data-1200="background-color:rgb(223,8,84);" data-1350="background-color:rgb(255,255,0);" id="mainnav">
		<div id="mainnavlinks">
			<span style="font-size:12px;font-weight:bold;line-height:1em;color:#000;">CASH Music</span><br />
			<a href="http://cashmusic.org/">Home</a> <a href="https://x.cashmusic.org/admin/">Log in</a>
		</div>
	</div>

	<div class="row" id="mainspc">
		<div class="section">
			<h1>Sign up.</h1><br />
			<div style="width:55% !important;">
				<?php echo $system_message; ?>
				<?php 
				if (!$success) {
				?>
				<form method="post">
					<input type="hidden" name="douseradd" value="yesdefinitely" />
					<label for="address">Email address</label>
					<input type="text" name="address" />
					<label for="username">Desired username</label>
					<input type="text" name="username" />
					<br />
					<a href="/terms/" target="_blank"><b>Our terms of service</b></a> / 
					<a href="/privacy/" target="_blank">Our privacy policy</a><br />
					<label for="termsread"><input type="checkbox" name="termsread" id="termsread" /> I have read and agree to the terms of service above.</label>
					<br />
					<input type="submit" value="Join CASH Music" />
				</form>
				<?php 
				}
				?>
			</div>
		</div>
	</div>

</body>
</html>