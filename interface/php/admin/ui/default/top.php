<!DOCTYPE html>
<html>
<head>
	<title>CASH Music: Admin</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="icon" type="image/png" href="http://cashmusic.org/images/icons/cash.png" />
	<link href="<?php echo WWW_BASE_PATH; ?>/ui/default/assets/css/admin.css" rel="stylesheet" type="text/css" />
	
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

	<script src="http://ajax.googleapis.com/ajax/libs/mootools/1.2.4/mootools-yui-compressed.js" type="text/javascript"></script>
	<script src="<?php echo WWW_BASE_PATH; ?>/ui/default/assets/scripts/flower/flower_core.js" type="text/javascript" id="hg_core"></script>
	<script src="<?php echo WWW_BASE_PATH; ?>/ui/default/assets/scripts/flower/flower_init.js" type="text/javascript"></script>
	<script src="<?php echo WWW_BASE_PATH; ?>/ui/default/assets/scripts/admin_misc.js" type="text/javascript"></script>

</head>
<body>

<div id="wrap">
	<div id="mainspc" class="<?php echo $specialcolor ?>">
		<div id="cash_sitelogo"><a href="http://cashmusic.org/"><img src="<?php echo WWW_BASE_PATH; ?>/ui/default/assets/images/cash.png" alt="CASH Music" width="30" height="30" /></a></div>
		<div id="navmenu">
			<div id="accountmenu"><a href="<?php echo WWW_BASE_PATH; ?>/"><b>Dashboard</b></a> <a href="<?php echo WWW_BASE_PATH; ?>/settings/">Settings</a> <a href="http://help.cashmusic.org/">Help</a> <a href="<?php echo WWW_BASE_PATH; ?>/logout/">Logout</a></div>
			<div class="navitem bgcolor1<?php echo ($dirname == 'elements' ? ' currentnav' : ''); ?>">
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
			<div class="navitem bgcolor5<?php echo ($dirname == 'calendar' ? ' currentnav' : ''); ?>">
				<h2><a href="<?php echo WWW_BASE_PATH; ?>/calendar/">Calendar</a></h2>
				planning<br />
				shows<br />
				guestlist
			</div>
		</div>
		
		<div id="pagecontent">
			<h1><?php echo $page_title ?></h1>
			<div id="tipspc">
				<div id="pageTips">
					<?php
						if ($page_tips == '') {
							echo 'There are no tips currently written for this page. If you\'d like to suggest a specific idea, please email <a href="mailto:help@cashmusic.org">help@cashmusic.org</a>.';
						} else {
							echo $page_tips;
						}
					?>
				</div>
				<a href="/help/" id="tiplink" class="flower_drawertoggle" rev="drawer:target=pageTips,altLinkText=Hide Tips">Tips for <b>this</b> page</a>
			</div>
			
			<?php
				$is_menu_set = ' class="nopagemenu"';
				$page_base = BASE_PAGENAME;
				if (strrpos(BASE_PAGENAME ,'_')) {
					if (isset($pagememu)) {
						$current_pagemenu = $pagememu;
					}
					$exploded_request = explode('_',BASE_PAGENAME);
					$page_base = $exploded_request[0];
					if (file_exists($pages_path . 'definitions/' . $page_base . '.php')) {
						include($pages_path . 'definitions/' . $page_base . '.php');
					}
				}
				if (isset($pagememu)) {
					if (is_array($pagememu)) {
						$is_menu_set = '';
			?>
					<div id="pagemenu">
						<?php
						
						foreach ($pagememu as $menutitle => $menuarray) {
							$menulevel = 1;
							echo "<h3>$menutitle</h3>";
							echo '<ul class="pagebasemenu">';
							foreach ($menuarray as $key => $value) {
								$new_menulevel = substr_count(trim($key,'/'), '/');
								if ($new_menulevel < $menulevel) {
									echo "</ul>";
								}
								if ($new_menulevel > $menulevel) {
									echo "<ul>";
								}
								if (str_replace('/','_',trim($key,'/')) == BASE_PAGENAME) {
									echo "<li style=\"margin-left:" . (16 * ($new_menulevel-1)) . "px;\">$value</li>";
								} else {
									echo "<li style=\"margin-left:" . (16 * ($new_menulevel-1)) . "px;\"><a href=\"" . WWW_BASE_PATH . "/$key\">$value</a></li>";
								}
								$menulevel = $new_menulevel;
							}
							echo '</ul>';
						}
						?>
					</div>
			<?php
					}
				}
			?>
			
			<div id="pagedisplay"<?php echo $is_menu_set; ?>>