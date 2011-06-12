<?php
/**
 * CASH Music Installer
 *
 * This single file acts as a simple HTML5/JS website, downloading and installing
 * the latest platform files, configuring the database, setting and environment
 * details, and ultimately removing itself and any extra source from the host
 * server. (I kind of feel like a jerk making it delete itself.)
 *
 * Usage: just upload and run.
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
*/
$cash_root_location = false;

function rrmdir($dir) { 
	if (is_dir($dir)) { 
	$objects = scandir($dir); 
	foreach ($objects as $object) { 
		if ($object != "." && $object != "..") { 
			if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
		} 
	} 
		reset($objects); 
		rmdir($dir); 
	} 
}
function findReplaceInFile($filename,$find,$replace) {
	if (is_file($filename)) {
		$file = file_get_contents($filename);
		$file = str_replace($find, $replace, $file);
		if (file_put_contents($filename, $file)) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

ob_start();

if (!isset($_POST['installstage'])) {
	/**
	 * BASE MARKUP
	 *
	 * This is the basic markup file that will push through all of the other 
	 * stages of the installer, all of which will be accessed via AJAX as this
	 * script reuses itself.
	*/
	?>
	<!DOCTYPE html> 
	<html> 
	<head> 
	<title>Install / CASH Music</title> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 

	<style type="text/css" /> 
	/* TAG RESETS */
	html {margin:0;padding:0;}
	body {color:#000;background:#fff url(../images/bg.jpg) center bottom repeat-x fixed;text-align:left;font:75%/1.5em helvetica,"helvetica neue",arial,sans-serif;margin:0;padding:0;min-height:300px;min-width:750px;}
	a {color:#999;text-decoration:none;}
	a:hover {text-decoration:underline;}
	code {display:block;padding:2em;margin:0 0 1.5em 0;background-color:#ddd;background-image:url(../images/currentnav.png);background-position:left top;background-repeat:no-repeat;margin:0 auto;}
	img {border:0;}
	ul {padding:0.5em 0 0.5em 1.5em;}
	p {margin:0 0 1.5em 0;}
	h1 {font-size:2.75em;line-height:1em;margin:0;padding:0;}
	h3 {font-size:1.5em;line-height:1em;margin:0;padding:0;padding-bottom:0.35em;}
	small {font-size:0.85em;line-height:1.25em;}
	small a {color:#000 !important;font-weight:bold;}
	table {font-size:0.85em;}

	/* GENERAL PAGE LAYOUT */
	#cash_sitelogo {position:absolute;top:8px;right:8px;text-align:right;overflow:hidden;height:30px;width:30px;z-index:2345;}
	#cash_sitelogo img {display:block;padding:0;margin:0;border:0;}

	/* FORMS */
	input {padding:6px 2% 6px 2%;border:1px solid #ccc;width:96%;}
	input:active, input:focus {outline:0;border:1px solid #888;}
	input.button, a.mockbutton {background-color:#ccc;padding:6px 18px 6px 18px;font-weight:bold;cursor:pointer;width:auto;}
	input.button:hover {background-color:#aaa;}
	input.checkorradio {width:auto;}
	select {padding:6px 3px 6px 3px;border:1px solid #ccc;width:100%;}
	select:active, select:focus {outline:2px solid #ff0;}
	label {font-size:0.85em;text-transform:uppercase;color:#999;}

	/* COLORS */
	.bgcolor1, div.usecolor1 input.button, div.usecolor1 a.mockbutton {background-color:#df0854;}
	.bgcolor2, div.usecolor2 input.button, div.usecolor2 a.mockbutton {background-color:#df9b08;}
	.bgcolor3, div.usecolor3 input.button, div.usecolor3 a.mockbutton {background-color:#aacd07;}
	.bgcolor4, div.usecolor4 input.button, div.usecolor4 a.mockbutton {background-color:#0891df;}
	.bgcolor5, div.usecolor5 input.button, div.usecolor5 a.mockbutton {background-color:#9b08df;}
	div.usecolor1 input.button:hover, div.usecolor1 a.mockbutton:hover {background-color:#cd074d;}
	div.usecolor2 input.button:hover, div.usecolor2 a.mockbutton:hover {background-color:#ca8c07;}
	div.usecolor3 input.button:hover, div.usecolor3 a.mockbutton:hover {background-color:#97b800;}
	div.usecolor4 input.button:hover, div.usecolor4 a.mockbutton:hover {background-color:#0080c9;}
	div.usecolor5 input.button:hover, div.usecolor5 a.mockbutton:hover {background-color:#8a00ca;}
	#pagecontent a, #pageTips a {color:#0891df;}
	div.usecolor1 #pagecontent a, div.usecolor1 #pageTips a {color:#cd074d;}
	div.usecolor2 #pagecontent a, div.usecolor2 #pageTips a {color:#ca8c07;}
	div.usecolor3 #pagecontent a, div.usecolor3 #pageTips a {color:#97b800;}
	div.usecolor4 #pagecontent a, div.usecolor4 #pageTips a {color:#0080c9;}
	div.usecolor5 #pagecontent a, div.usecolor5 #pageTips a {color:#8a00ca;}
	a.mockbutton {color:#000 !important;font-size:0.9em;}
	a.mockbutton:hover {text-decoration:none !important;}

	/* FOOTER (base code taken from cssstickyfooter.com) */
	* {margin-top:0;padding:0;} 
	html, body, #wrap {height:100%;}
	body > #wrap {height:auto;min-height:100%;}
	#mainspc {padding-bottom: 60px;padding-top:150px;width:400px;margin:0 auto;}
	#footer {position:relative;margin-top:-36px;height:36px;color:#666;text-align:left;font-size:0.9em;line-height:1em;clear:both;background-color:#000;}
	#footer p {padding:12px 8px 0px 8px;}
	#footer a {color:#666;margin-left:24px;}
	#footer a:hover {color:#fff;}
	#footer .donatelink {color:#999;}

	/* ACCESSIBILITY STUFFS */
	* a:active, * a:focus, #footer a.donatelink:active, #footer a.donatelink:focus, input.checkorradio:focus, input.checkorradio:active
	{outline:2px solid #ff0;background-color:#ff0;color:#000;}
	</style>
	<link rel="icon" type="image/png" href="http://cashmusic.org/assets/images/icons/cash.png" /> 

	</head> 

	<body> 

	<div id="wrap"> 
	 	<div id="mainspc">
		<h1>Hi.</h1>
		</div>
	</div>

	<div id="footer">
		<p><b>&copy; 2011 CASH Music.</b> All our code is open-source. <a href="http://cashmusic.org/why/" style="margin-left:0;">Learn more</a>. <a href="http://help.cashmusic.org/">Get help</a> <a href="http://cashmusic.org/donate" class="donatelink"><b>Donate</b></a></p>
	</div>

	</body>
	</html>
	<?php
} else {
	// create the directory structure: remove any existing source files and re-download
	// we'll make a proper update script later. 
	$admin_dir = dirname($_SERVER['REQUEST_URI']) . '/admin';
	$source_dir = dirname($_SERVER['REQUEST_URI']) . '/source';
	if (!$cash_root_location) {
		// if $cash_root_location is not set above make a directory above the root
		$cash_root_location = dirname($_SERVER['DOCUMENT_ROOT']) . '/cashmusic';
	}
	if (is_dir('./source')) {
		rrmdir('./source');
		echo 'removed old source directory at ' . $source_dir . '<br />';
	}
	if (is_dir('./admin')) {
		rrmdir('./admin');
		echo 'removed old admin directory at ' . $admin_dir . '<br />';
	}
	if (mkdir('./source')) {
		echo 'created directory: ' . $source_dir . '<br />';
	} else {
		echo 'error creating directory: ' . $source_dir . '<br />';
	}
	// if the cash root location doesn't exist, create it. if it does, check for core
	// and blow it away if it exists
	if (!is_dir($cash_root_location)) {
		if (mkdir($cash_root_location)) {
			echo 'created directory: ' . $cash_root_location . '<br />';
		} else {
			echo 'error creating directory: ' . $cash_root_location . '<br />';
		}
	} else {
		if (is_dir($cash_root_location . '/core')) {
			rrmdir($cash_root_location . '/core');
			echo 'removed old core directory at ' . $cash_root_location . '/core' . '<br />';
		}
	}


	// download the latest source from github
	$repo = json_decode(file_get_contents('https://github.com/api/v2/json/blob/all/cashmusic/DIY/master'));
	$files = array_keys((array)$repo->blobs);
	echo 'copying files from github repo: ';
	foreach ($files as $file) {
		$path = pathinfo($file);
		if (!is_dir('./source/'.$path['dirname'])) mkdir('./source/'.$path['dirname'],0777,true);
		copy('https://raw.github.com/cashmusic/DIY/master/'.$file,'./source/'.$file);
		echo '.';
	}
	echo '<br />';


	// setup database, remove sql folder, modify settings files
	if (findReplaceInFile('./source/interfaces/php/admin/.htaccess','RewriteBase /admin','RewriteBase ' . $admin_dir)) {
		echo 'set RewriteBase in .htaccess<br />';
	} else {
		echo 'could not set RewriteBase in .htaccess<br />';
	}
	if (findReplaceInFile('./source/interfaces/php/admin/constants.php','$cashmusic_root = $root . "/../../../core/php/cashmusic.php','$cashmusic_root = "' . $cash_root_location . '/core/cashmusic.php')) {
		echo 'set $cashmusic_root in constants.php<br />';
	} else {
		echo 'could not set $cashmusic_root in constants.php<br />';
	}
	if (findReplaceInFile('./source/interfaces/php/admin/constants.php','define(\'ADMIN_WWW_BASE_PATH\', \'/admin','define(\'ADMIN_WWW_BASE_PATH\', \'' . $admin_dir)) {
		echo 'set ADMIN_WWW_BASE_PATH in constants.php<br />';
	} else {
		echo 'could not set ADMIN_WWW_BASE_PATH in constants.php<br />';
	}

	// move source files into place
	if (rename('./source/core/php', $cash_root_location . '/core')) {
		echo 'moved core files into place at: ' . $cash_root_location . '/core' . '<br />';
	} else {
		echo 'error moving core files to: ' . $cash_root_location . '/core' . '<br />';
	}
	if (rename('./source/interfaces/php/admin', './admin')) {
		echo 'moved admin files into place at: ' . $admin_dir . '<br />';
	} else {
		echo 'error moving admin files to: ' . $admin_dir . '<br />';
	}

}

ob_end_flush(); 
?>