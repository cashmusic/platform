<h3>Main Seed Include:</h3>
<p>
	This on line gets Seed up and running. It should be included at the very top 
	of all Seed pages, before other code:
</p>
<code>
	&lt?php include('<?php echo realpath(SEED_PATH); ?>'); // Initialize Seed ?&gt
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
	&lt?php include('<?php echo realpath(SEED_ROOT); ?>/settings/debug/seed_debug.php'); // Seed Debug ?&gt
</code>