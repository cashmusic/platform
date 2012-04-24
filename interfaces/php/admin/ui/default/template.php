<!DOCTYPE html>
<html>
<head>
	<title><?php echo $cash_admin->page_data['title'] ?></title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="cashmusic-status-code" content="<?php echo $cash_admin->page_data['status_code']; ?>" />
	<meta name="cashmusic-status-uid" content="<?php echo $cash_admin->page_data['status_uid']; ?>" />
	
	<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />
	<link href="<?php echo $cash_admin->page_data['www_path']; ?>/ui/default/assets/css/admin.css" rel="stylesheet" type="text/css" />
	
	<script src="https://ajax.googleapis.com/ajax/libs/mootools/1.3.2/mootools-yui-compressed.js" type="text/javascript"></script>
	<script src="<?php echo $cash_admin->page_data['www_path']; ?>/ui/default/assets/scripts/admin_misc.js" type="text/javascript"></script>
</head>
<body>

<div id="wrap">
	<div id="mainspc" class="<?php echo $cash_admin->page_data['specialcolor'] ?>">
		<div id="chromespc">
			<div id="accountspc">
				<div id="accountmenu">
					<span class="icon cog"></span> <?php echo $admin_primary_cash_request->sessionGet('cash_effective_user_email'); ?>
					<ul>
						<li><a href="<?php echo $cash_admin->page_data['www_path']; ?>/account/">Your account</a></li>
						<li><a href="<?php echo $cash_admin->page_data['www_path']; ?>/settings/">System settings</a></li>
						<li><a href="<?php echo $cash_admin->page_data['www_path']; ?>/logout/">Log out</a></li>
					</ul>
				</div>
			</div>
			<div id="navmenu">
				<div class="navitem bgcolor1<?php echo ($cash_admin->page_data['section_name'] == 'elements' ? ' currentnav' : ''); ?>">
					<h2><a href="<?php echo $cash_admin->page_data['www_path']; ?>/elements/">Elements</a></h2>
					<div class="navitemdescription">
						create<br />embed
					</div>
				</div>
				<div class="navitem bgcolor2<?php echo ($cash_admin->page_data['section_name'] == 'assets' ? ' currentnav' : ''); ?>">
					<h2><a href="<?php echo $cash_admin->page_data['www_path']; ?>/assets/">Assets</a></h2>
					<div class="navitemdescription">
						upload<br />organize
					</div>
				</div>
				<div class="navitem bgcolor3<?php echo ($cash_admin->page_data['section_name'] == 'people' ? ' currentnav' : ''); ?>">
					<h2><a href="<?php echo $cash_admin->page_data['www_path']; ?>/people/">People</a></h2>
					<div class="navitemdescription">
						manage<br />list
					</div>
				</div>
				<div class="navitem bgcolor4<?php echo ($cash_admin->page_data['section_name'] == 'commerce' ? ' currentnav' : ''); ?>">
					<h2><a href="<?php echo $cash_admin->page_data['www_path']; ?>/commerce/">Commerce</a></h2>
					<div class="navitemdescription">	
						sell<br />fulfill
					</div>
				</div>
				<div class="navitem bgcolor5<?php echo ($cash_admin->page_data['section_name'] == 'calendar' ? ' currentnav' : ''); ?>">
					<h2><a href="<?php echo $cash_admin->page_data['www_path']; ?>/calendar/">Calendar</a></h2>
					<div class="navitemdescription">
						shows<br />guestlist
					</div>
				</div>
			</div>
			
			<div id="tipspc">
				<div id="pagetips">
					<?php echo $cash_admin->page_data['page_tip']; ?>
					<br /><br />
					<a id="tipscloselink" href="#">close</a>
				</div>
			</div>
		</div>
		
		<div id="pagecontent">
			<a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/" id="dashboardlink">Main Page</a>
			<div id="helpmenu">
				<span id="mainhelplink">Help</span>
				<ul>
					<li><a href="<?php echo $cash_admin->page_data['www_path']; ?>/help/" id="tipslink">Tips for <b>this</b> page</a></li>
					<li><a href="<?php echo $cash_admin->page_data['www_path']; ?>/help/gettingstarted/">Getting started</a></li>
					<li><a href="<?php echo $cash_admin->page_data['www_path']; ?>/help/">FAQ</a></li>
					<li><a href="http://help.cashmusic.org/">help.cashmusic.org</a></li>
				</ul>
			</div>
			
			<h1><?php echo $cash_admin->page_data['title'] ?></h1>
			<?php if ($cash_admin->page_data['section_menu']) { ?>
					<div id="pagemenu">
						<?php echo $cash_admin->page_data['section_menu']; ?>
					</div>
			<?php } ?>

			<div id="pagedisplay"<?php if (!$cash_admin->page_data['section_menu']) { echo ' class="nopagemenu"'; } ?>>
				<?php echo $cash_admin->page_data['content'] ?>
			</div>
			<div class="clearfix">.</div>
		</div>

	</div>

</div>

<div id="footer">
	<p><b>&copy; 2011 CASH Music.</b> All our code is open-source. <a href="<?php echo $cash_admin->page_data['www_path']; ?>/licenses/" style="margin-left:0;">Learn more</a>. <a href="http://cashmusic.org/donate" class="donatelink"><b>Donate</b></a></p>
</div>

</body>
</html>