<!DOCTYPE html>
<html lang="en">
<head> 
<title>CASH Music / Test Suite</title>
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

		<h3>Test suite</h3><br />

		<p>
			The platform test suite, written in SimpleTest, is a command-line based set of scripts that installs the 
			platform with known settings, deploys a clean database, runs tests, then restores working settings. It's 
			handy, but not quite ready to be run in a browser. We're working on it. 
		</p><p>
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
				
				<div style="width:100%;height:auto;overflow-x:scroll;background-color:#fff;padding:12px;border-radius:6px;">
					<?php
					/*
					if (empty($output)) {
						$output = 'There was an error running tests.';
					}
					echo "<pre>$output</pre>";
					*/
					?>
					<pre>cd /vagrant &amp;&amp; make test</pre>
				</div>

			</div>
		</div>

		<p>
			<br />
			You should see line-by-line success reports, details of any failures, and something like this:
		</p>
		<pre>Test cases run: 23/23, Passes: 651, Failures: 0, Exceptions: 0</pre>
		<pre>Result: PASS</pre>

		<p>
			<br />
			Please make sure the test suite is fully passing before submitting any pull requests, and happy testing!
		</p>

		<br /><br /><br />
	</div>

	<div id="bottomspc"></div>
</body> 
</html>