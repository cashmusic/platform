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
			<li><a href="/interfaces/admin/"><i class="icon icon-pencil"></i><span>Login</span></a></li>
			<li><a href="/"><i class="icon icon-certificate"></i><span>Home</span></a></li>
		</ul>
</div>

	<div id="mainspc" class="row">

		<h1>Test suite</h1>
		<h2>
			The platform test suite, written in SimpleTest, is a command-line based set of scripts that installs the 
			platform with known settings, deploys a clean database, runs tests, then restores working settings. It's 
			handy, but not quite ready to be run in a browser. We're working on it. 
		</h2>
		<p>
			To run the tests, first ssh into your vagrant instance ('vagrant ssh') then type:
		</p>

		<?php
			/*
			chdir('/vagrant');
			$output = shell_exec('make test');
			*/
		?>

		<div class="row">
			<div class="twelve columns">
				
				<code>
					<?php
					/*
					if (empty($output)) {
						$output = 'There was an error running tests.';
					}
					echo "<pre>$output</pre>";
					*/
					?>
					<pre>cd /vagrant &amp;&amp; make test</pre>
				</code>

			</div>
		</div>

		<p>
			<br />
			You should see line-by-line success reports, details of any failures, and something like this:
		</p>
		<code>
		<pre>Test cases run: 23/23, Passes: 651, Failures: 0, Exceptions: 0</pre>
		<pre>Result: PASS</pre>
		</code>
		<h2>
			Please make sure the test suite is fully passing before submitting any pull requests, and happy testing!
		</h2>

		<br /><br /><br />
	</div>

	<div id="bottomspc"></div>
</body> 
</html>