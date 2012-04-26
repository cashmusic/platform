<h3>Using the platform</h3>
<p>
This one line gets the platform up and running. It should be included at the very top
of all pages, before any other code:
</p>
<code>
	&lt?php include('<?php echo realpath(CASH_PLATFORM_PATH); ?>'); // CASH Music ?&gt
</code>
<br />
<h3>Debug Include:</h3>
<p>
	Include this if you need to see what's happening under the hood. <b>DO NOT 
	INCLUDE IT ON LIVE PAGES!</b> The debug include will display all current Seed 
	request data as well as the contents of the _SESSION array. It also shows a 
	pretty dandelion seed picture.
</p>
<code>
	&lt?php include('<?php echo realpath(CASH_PLATFORM_ROOT); ?>/settings/debug/cashmusic_debug.php'); // CASH Music Debug ?&gt
</code>
