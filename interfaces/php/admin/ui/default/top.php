<!DOCTYPE html>
<html>
<head>
	<title><?php echo AdminHelper::getPageTitle(); ?></title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<?php
		if (isset($_SESSION['cash_last_response'])) {
			echo '<meta name="cashmusic-status-code" content="' . $_SESSION['cash_last_response']['status_code'] . '" />' . "\n\t";
			echo '<meta name="cashmusic-status-uid" content="' . $_SESSION['cash_last_response']['status_uid'] . '" />' . "\n\t";
			echo '<meta name="cashmusic-contextual-message" content="' . $_SESSION['cash_last_response']['contextual_message'] . '" />' . "\n\t";
		}
	?>
	
	<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />
	<link href="<?php echo ADMIN_WWW_BASE_PATH; ?>/ui/default/assets/css/admin.css" rel="stylesheet" type="text/css" />
	
	<?php
		$fixed_dirnames = str_replace(ADMIN_WWW_BASE_PATH,'',$_SERVER['REQUEST_URI']);
		$dirnames = explode('/',$fixed_dirnames);
		if ($dirnames) {
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
		}
	?>

	<script src="https://ajax.googleapis.com/ajax/libs/mootools/1.3.2/mootools-yui-compressed.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_WWW_BASE_PATH; ?>/ui/default/assets/scripts/admin_misc.js" type="text/javascript"></script>

</head>
<body>

<div id="wrap">
	<div id="mainspc" class="<?php echo $specialcolor ?>">
		<div id="chromespc">
			<div id="accountspc">
				<div id="accountmenu">
					<span class="icon cog"></span> <?php echo $admin_primary_cash_request->sessionGet('cash_effective_user_email'); ?>
					<ul>
						<li><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/account/">Your account</a></li>
						<li><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/settings/">System settings</a></li>
						<li><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/logout/">Log out</a></li>
					</ul>
				</div>
			</div>
			<div id="navmenu">
				<div class="navitem bgcolor1<?php echo ($dirname == 'elements' ? ' currentnav' : ''); ?>">
					<h2><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/elements/">Elements</a></h2>
					<div class="navitemdescription">
						create<br />
						embed<br />
					</div>
				</div>
				<div class="navitem bgcolor2<?php echo ($dirname == 'assets' ? ' currentnav' : ''); ?>">
					<h2><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/assets/">Assets</a></h2>
					<div class="navitemdescription">
						upload<br />
						organize<br />
					</div>
				</div>
				<div class="navitem bgcolor3<?php echo ($dirname == 'people' ? ' currentnav' : ''); ?>">
					<h2><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/people/">People</a></h2>
					<div class="navitemdescription">
						manage<br />
						list
					</div>
				</div>
				<div class="navitem bgcolor4<?php echo ($dirname == 'commerce' ? ' currentnav' : ''); ?>">
					<h2><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/commerce/">Commerce</a></h2>
					<div class="navitemdescription">	
						sell<br />
						fulfill<br />
					</div>
				</div>
				<div class="navitem bgcolor5<?php echo ($dirname == 'calendar' ? ' currentnav' : ''); ?>">
					<h2><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/calendar/">Calendar</a></h2>
					<div class="navitemdescription">
						shows<br />
						guestlist
					</div>
				</div>
			</div>
			
			<div id="tipspc">
				<div id="pagetips">
					<?php
						echo AdminHelper::getPageTipsString();
					?>
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
					<li><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/help/" id="tipslink">Tips for <b>this</b> page</a></li>
					<li><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/help/gettingstarted/">Getting started</a></li>
					<li><a href="<?php echo ADMIN_WWW_BASE_PATH; ?>/help/">FAQ</a></li>
					<li><a href="http://help.cashmusic.org/">help.cashmusic.org</a></li>
				</ul>
			</div>
			
			<h1><?php echo AdminHelper::getPageTitle(); ?></h1>
			<?php
				$is_menu_set = ' class="nopagemenu"';
				$page_menu = AdminHelper::buildSectionNav();
				if ($page_menu) {
					$is_menu_set = '';
			?>
					<div id="pagemenu">
						<?php
							echo $page_menu;
						?>
					</div>
			<?php
				}
			?>

			<div id="pagedisplay"<?php echo $is_menu_set; ?>>