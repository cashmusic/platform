<?php
	include('../Seed.php');

	// will replace with persistent-storage unlock mechanism:
	if (isset($_GET['down'])) {
		$test = new SeedRequest(array('seed_request_type' => 'asset', 'seed_action' => 'redirect','asset_id' => 1));
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>CASH Music : Seed Test</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/demo.css" rel="stylesheet" type="text/css" />

</head>

<body>

<div id="mainspc">
	<h1>BAD BOOKS</h1>
	<h2>our mailing list</h2>

	<?php if ($seed_request->response['status_uid'] == 'emaillist_signup_200') { // SEED email signup success ?>
		Thanks! You're all signed up. Here's your download: 
		<br /><br />
		<a href="?down=xx" class="download">“You Wouldn’t Have To Ask” MP3</a>
	<?php } else { ?>
		<?php if ($seed_request->response['status_uid'] == 'emaillist_signup_400') { // SEED invalid address ?>
			<div class="error">
				Sorry, that email address wasn't valid. Please try again.
			</div>
		<?php } // SEED default state: signup form ?>
		<p>
			Sign up now for our mailing list and receive a free download.
		</p>
	
		<form method="post" action="#"> 
			<input type="text" name="address" value="" style="width:18em;" /> 
			<input type="hidden" name="seed_request_type" value="emaillist" /> 
			<input type="hidden" name="seed_action" value="signup" /> 
			<input type="hidden" name="list_id" value="1" /> 
			<input type="hidden" name="verified" value="1" />  	
			<input type="submit" value="sign me up" class="button" /><br />  
		</form> 
		<div class="notation">
			We won't share, sell, or be jerks with your email address.
		</div>
	<?php } ?>

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