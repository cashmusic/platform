<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>CASH Music: Admin</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />
	<link href="<?php echo WWW_BASE_PATH; ?>/_assets/css/admin.css" rel="stylesheet" type="text/css" />
	
	<?php
		$fixed_dirnames = str_replace(WWW_BASE_PATH,'',$_SERVER['REQUEST_URI']);
		$dirnames = explode('/',$fixed_dirnames);
		$dirname = $dirnames[1];
		$specialcolor = '';
		if ($dirname == 'elements') {
			$specialcolor = ' usecolor1';
		} elseif ($dirname == 'assets') {
			$specialcolor = ' usecolor2';
		} elseif ($dirname == 'people') {
			$specialcolor = ' usecolor3';
		} elseif ($dirname == 'commerce') {
			$specialcolor = ' usecolor4';
		} elseif ($dirname == 'calendar') {
			$specialcolor = ' usecolor5';
		}
	?>

	<script src="http://ajax.googleapis.com/ajax/libs/mootools/1.2.3/mootools-yui-compressed.js" type="text/javascript"></script>
	<script src="http://mercury.cashmusic.org/scripts/hg2/hg_core.js" type="text/javascript" id="hg_core"></script>
	<script src="http://mercury.cashmusic.org/scripts/hg2/hg_init.js" type="text/javascript"></script>
	<script src="<?php echo WWW_BASE_PATH; ?>/_assets/scripts/admin_misc.js" type="text/javascript"></script>

</head>
<body>

<div id="wrap">
	<div id="mainspc" class="clearfix<?php echo $specialcolor ?>">
		<div id="cash_sitelogo"><a href="http://cashmusic.org/"><img src="<?php echo WWW_BASE_PATH; ?>/_assets/images/cash.png" alt="CASH Music" width="30" height="30" /></a></div>
		<div id="accountmenu"><a href="<?php echo WWW_BASE_PATH; ?>/"><b>Dashboard</b></a> <a href="<?php echo WWW_BASE_PATH; ?>/settings/">Settings</a> <a href="<?php echo WWW_BASE_PATH; ?>/help/">Help</a> <a href="<?php echo WWW_BASE_PATH; ?>/logout/">Logout</a></div>
		<div id="navmenu">
			<div class="navitem bgcolor1<?php echo ($dirname == 'content' ? ' currentnav' : ''); ?>">
				<h2><a href="<?php echo WWW_BASE_PATH; ?>/elements/">Elements</a></h2>
				pages<br />
				widgets<br />
				plugins
			</div>
			<div class="navitem bgcolor2<?php echo ($dirname == 'assets' ? ' currentnav' : ''); ?>">
				<h2><a href="<?php echo WWW_BASE_PATH; ?>/assets/">Assets</a></h2>
				upload<br />
				tag<br />
				share
			</div>
			<div class="navitem bgcolor3<?php echo ($dirname == 'people' ? ' currentnav' : ''); ?>">
				<h2><a href="<?php echo WWW_BASE_PATH; ?>/people/">People</a></h2>
				gather<br />
				mail<br />
				list
			</div>
			<div class="navitem bgcolor4<?php echo ($dirname == 'commerce' ? ' currentnav' : ''); ?>">
				<h2><a href="<?php echo WWW_BASE_PATH; ?>/commerce/">Commerce</a></h2>
				review<br />
				fulfill<br />
				pledge
			</div>
			<div class="navitem bgcolor5<?php echo ($dirname == 'analytics' ? ' currentnav' : ''); ?>">
				<h2><a href="<?php echo WWW_BASE_PATH; ?>/calendar/">Calendar</a></h2>
				planning<br />
				shows<br />
				guestlist
			</div>
			
			<div id="tipspc">
				<div id="pageTips">
					<?php
						if ($pageTips == '') {
							echo 'There are no tips currently written for this page. If you\'d like to suggest a specific idea, please email <a href="mailto:help@cashmusic.org">help@cashmusic.org</a>.';
						} else {
							echo $pageTips;
						}
					?>
				</div>
				<a href="/help/" id="tiplink" class="hg_drawertoggle" rev="drawer:target=pageTips,altLinkText=Hide Tips">Tips for using <i>this page</i></a>
			</div>
		</div>
		
		<div id="pagecontent">
			<h1><?php echo $pagetitle ?></h1>