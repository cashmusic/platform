<?php
/**
 * CASH Music Updater
 *
 * To be launched from within the platform.
 * 
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
*/
session_start();
$_SESSION['copying'] = false; // we'll use this in the AJAX copy loops later
$_SESSION['release_id'] = 'stable';

include_once('./admin/constants.php');
include_once(CASH_PLATFORM_PATH);

$cash_root_location = str_replace('/cashmusic.php','',CASH_PLATFORM_PATH);

// recursive rmdir:
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

function getBaseURL() {
	return 'http'.((empty($_SERVER['HTTPS'])&&$_SERVER['SERVER_PORT']!=443)?'':'s') 
		  .'://'.$_SERVER['HTTP_HOST'];
}

function determinedCopy($source,$dest,$retries=4) {
	$retries++;
	if (!$_SESSION['copying']) {
		$_SESSION['copying'] = true;
		while($retries > 0) {
			if (function_exists('curl_init')) {
				$ch = curl_init();
				$timeout = 15;
				$userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.5; rv:7.0) Gecko/20100101 Firefox/7.0';
			
				@curl_setopt($ch,CURLOPT_URL,$source);
			
				@curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
				@curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				@curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
				@curl_setopt($ch, CURLOPT_FAILONERROR, true);
				@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				@curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				@curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
				@curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			
				$destfile = fopen($dest, 'wb'); 
				@curl_setopt($ch, CURLOPT_FILE, $destfile);
			
				if (curl_exec($ch)) {
					fclose($destfile); 
					curl_close($ch);
					return true;
				} else {
					fclose($destfile); 
					if (file_exists($dest)) {
						unlink($dest);
					}
					curl_close($ch);
					sleep(3);
				}
			} elseif (ini_get('allow_url_fopen')) {
				if (@copy($source,$dest)) {
					chmod($dest,0755);
					$_SESSION['copying'] = false;
					return true;
				} else {
					sleep(1);
				}
			}
			$retries--;
		}
		$_SESSION['copying'] = false;
		return false;
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
	<title>Update / CASH Music</title> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 

	<style type="text/css" /> 
	/* TAG RESETS */
	html {margin:0;padding:0;}
	body {color:#231F20;background-color:#fafaf8;text-align:left;font:14px/1.5em "helvetica neue",helvetica,arial,sans-serif;margin:0;padding:0;min-height:300px;min-width:750px;text-rendering:optimizelegibility;}
	a {color:#999;text-decoration:none;}
	a:hover {text-decoration:underline;color:#000 !important;}
	code {display:block;padding:2em;margin:0 0 1.5em 0;background-color:#ddd;background-image:url(../images/currentnav.png);background-position:left top;background-repeat:no-repeat;margin:0 auto;}
	img {border:0;}
	ul {padding:0.5em 0 0.5em 1.5em;}
	p {margin:0 0 1.5em 0;}
	h1 {font-size:32px;line-height:1.125em;margin:0;padding:0;}
	h2 {font-size:24px;line-height:1.125em;margin:0;padding:0;}
	h3 {font-size:1.5em;line-height:1em;margin:20px 0 0 0;padding:0;padding-bottom:0.35em;}
	small {font-size:0.85em;line-height:1.25em;}
	small a {color:#000 !important;font-weight:bold;}
	table {font-size:0.85em;}

	/* GENERAL PAGE LAYOUT */
	#topstrip {position:absolute;top:0;left:0;width:100%;height:6px;z-index:400;background-color:#000;color:#666;text-align:right;}
	.altcopystyle {font:italic 12px/1.5em georgia, times, serif;color:#4d4d4f;}
	.fadedtext {color:#9c9ca9 !important;}

	/* COLORS */
	.bgcolor0 {background-color:#999;}
	.bgcolor1, div.usecolor1 input.button, div.usecolor1 a.mockbutton {background-color:#df0854;}
	.bgcolor2, div.usecolor2 input.button, div.usecolor2 a.mockbutton {background-color:#df9b08;}
	.bgcolor3, div.usecolor3 input.button, div.usecolor3 a.mockbutton {background-color:#aacd07;}
	.bgcolor4, div.usecolor4 input.button, div.usecolor4 a.mockbutton {background-color:#0891df;}
	.bgcolor5, div.usecolor5 input.button, div.usecolor5 a.mockbutton {background-color:#9b08df;}
	div.usecolor1 a, h2.usecolor1 {color:#cd074d;}
	div.usecolor2 a, h2.usecolor2 {color:#ca8c07;}
	div.usecolor3 a, h2.usecolor3 {color:#97b800;}
	div.usecolor4 a, h2.usecolor4 {color:#0080c9;}
	div.usecolor5 a, h2.usecolor5 {color:#8a00ca;}
	a.usecolor0 {color:#999 !important;}
	a.usecolor1 {color:#cd074d !important;}
	a.usecolor2 {color:#ca8c07 !important;}
	a.usecolor3 {color:#97b800 !important;}
	a.usecolor4 {color:#0080c9 !important;}
	a.usecolor5 {color:#8a00ca !important;}
	a.mockbutton {color:#fff !important;font-size:13px;}
	a.mockbutton:hover {background-color:#000 !important;text-decoration:none !important;}
	div.callout a {font-weight:bold;color:#999;}
	div.callout a:hover {font-weight:bold;color:#231F20;}
	* a.needsconfirmation:hover {color:#f00 !important;}

	/* FORMS */
	form span {line-height:2.5em;}
	input,textarea,select {font:italic 13px/1.25em georgia, times, serif !important;padding:8px 2% 8px 2%;border:1px solid #dddddf;width:96%;}
	input:active, input:focus, textarea:focus {outline:0;border:1px solid #888;}
	input.button, a.mockbutton {background-color:#ccc;padding:8px 18px 8px 18px !important;font:bold 13px/1.25em helvetica,arial,sans-serif !important;cursor:pointer;width:auto !important;border:none;color:#fff;}
	input.button:hover {background-color:#000 !important;color:#fff;}
	input.checkorradio {width:auto !important;margin-top:8px;}
	select {height:34px;line-height:34px;width:100%;padding:8px;border:none;background-color:#ededef;background-image:linear-gradient(top, #dfdfdf 0%, #efefef 100%);background-image:-moz-linear-gradient(top, #dfdfdf 0%, #efefef 100%);border-radius:5px;}
	select option {padding:8px;}
	select:active, select:focus {outline:2px solid #ff0;}
	label {font-size:11px;text-transform:uppercase;color:#9c9ca9;}
	
	/* PROGRESS BAR */
	#progressspc {position:relative;width:400px;height:30px;font-size:18px;line-height:30px;font-weight:bold;margin:0 auto;overflow:hidden;color:#eee;background-color:#ccc;visibility:hidden;}
	#progressbar {position:absolute;top:0;left:0;width:0;height:30px;color:#fff;background-color:#0080c9;z-index:100;overflow:hidden;}
	p.progressamount {position:absolute;top:0;left:0;margin:0;padding:0 0 0 8px;z-index:10;}

	/* FOOTER (base code taken from cssstickyfooter.com) */
	* {margin-top:0;padding:0;} 
	html, body, #wrap {height:100%;}
	body > #wrap {height:auto;min-height:99%;}
	#mainspc {padding-bottom:40px;padding-top:150px;width:400px;margin:0 auto;}
	#footer {position:relative;margin-top:-32px;height:36px;color:#babac4;text-align:left;font-size:11px;line-height:1em;clear:both;background-color:transparent;}
	#footer p {padding:12px 8px 0px 12px;}
	#footer a {color:#babac4;margin-left:24px;}
	#footer a:hover {color:#231F20;}
	#footer .donatelink {color:#aaa;}

	/* ACCESSIBILITY STUFFS */
	* a:active, * a:focus, #footer a.donatelink:active, #footer a.donatelink:focus, input.checkorradio:focus, input.checkorradio:active
	{outline:2px solid #ff0;background-color:#ff0;color:#000;}
	</style>
	<link rel="icon" type="image/png" href="https://cashmusic.org/assets/images/icons/cash.png" /> 

	<script src="https://ajax.googleapis.com/ajax/libs/mootools/1.3.1/mootools-yui-compressed.js" type="text/javascript"></script> 
	<script type="text/javascript"> 
		var currentColor = 1;
		var progressIsVisible = false;
	
		function prepPage() {
			if (document.id('nextstepform')) {
				document.id('nextstepform').addEvent('submit', function(e) {
					if (e) { e.stop(); }
					var targetEl = document.id('mainspc').removeClass('usecolor'+currentColor);
					if (document.id('installstageinput')) {
						currentColor = document.id('installstageinput').get('value');
					}

					var myHTMLRequest = new Request.HTML({onComplete: function(response){
						targetEl.addClass('usecolor'+currentColor);
						targetEl.empty().adopt(response);
						document.id('mainspc').fade('in');
						prepPage();
					}});
					
					var that = this;
					if (document.id('installstagefade')) {
						document.id('mainspc').fade('out');
						(function(){myHTMLRequest.post(that);}).delay(600);
					} else {
						myHTMLRequest.post(that);
					}
				});
			}
		}

		function showProgress(amount) {
			if (document.id('progressspc').get('visibility') !== 'visible') {
				document.id('progressspc').fade('in');
			}
			if (amount > 0) {
				$$('p.progressamount').each(function(p){
					p.set('text',amount + '%');
				});
				document.id('progressbar').tween('width',Math.floor(400 * (amount / 100)));
			}
		}

		function hideProgress() {
			document.id('progressspc').fade('out');
		}

		window.addEvent('domready',function() {
			document.id('progressspc').set('tween', {duration: 45});
			prepPage();
		});
	</script>

	</head> 

	<body> 
	<div id="topstrip">&nbsp;</div>
	<div id="wrap"> 
	 	<div id="mainspc" class="usecolor1">
			<h1>Oh, Hi Again.</h1>
			<p>
				Upgrading? No worries, it's easy. This script will work a lot like the original 
				installer. First it'll grab new updates, and download new files. When that's done 
				it'll swap out your old files for the new and you'll have the latest shiniest things
				without losing any data along the way.
			</p><p>
				As with anything monkeying in your databases, you should back up your current database 
				now if possible.
			</p>
		
			<br /><br />
			<div class="nextstep">
				<div class="altcopystyle fadedtext" style="margin-bottom:6px;">Whenever you're ready:</div>
				<form action="" method="post" id="nextstepform">
					<input type="hidden" name="installstage" id="installstageinput" value="2" />
					<input type="hidden" id="installstagefade" value="1" />
					<input type="submit" class="button" value="Update" />
				</form>
			</div>
		</div>
		
		<div id="progressspc"><p class="progressamount">0%</p><div id="progressbar"><p class="progressamount">0%</p></div></div>
	</div>

	<div id="footer">
		<p><b>&copy; 2012 CASH Music.</b> All our code is open-source. <a href="http://cashmusic.org/why/" style="margin-left:0;">Learn more</a>. <a href="http://help.cashmusic.org/">Get help</a> <a href="http://cashmusic.org/donate" class="donatelink"><b>Donate</b></a></p>
	</div>

	</body>
	</html>
	<?php
} else {
	/**
	 * AJAX FUNCTIONALITY (Pretty much all the actual install steps)
	 *
	 * Basic output that will replace the initial message in the "mainspc" div above.
	*/
	switch ($_POST['installstage']) {
		case "2":
			/**
			 * INSTALL CURRENT SOURCE
			 *
			 * Don't even bother checking for git. Set up directories and hit the
			 * github API, grab the files, looped AJAX delay so we don't make
			 * anyone at github mad.
			*/
			$source_message = '<h1>Copying files.</h1><p>'
				. 'This should take a few minutes.</p>'
				. '<div class="altcopystyle fadedtext" style="margin-bottom:6px;">Progress:</div>';
			// as long as determinedCopy isn't spinning we can copy files from the repo
			if (!$_SESSION['copying']) {
				if (!file_exists('./release_profile.json')) {
					// create the directory structure: remove any existing source files and re-download
					// we'll make a proper update script later.
					if (is_dir('./update')) {
						rrmdir('./update');
					}
					if (mkdir('./update',0755,true)) {
						// get repo from github, strip unnecessary files and write manifest:
						if (determinedCopy('http://cashmusic.s3.amazonaws.com/releases/'.$_SESSION['release_id'].'/release_profile.json','./release_profile.json') && determinedCopy('http://cashmusic.s3.amazonaws.com/releases/'.$_SESSION['release_id'].'/updates.json','./updates.json')) {
							echo $source_message;
							echo '<form action="" method="post" id="nextstepform"><input type="hidden" name="installstage" id="installstageinput" value="2" /></form>';
							echo '<script type="text/javascript">showProgress(0);(function(){document.id("nextstepform").fireEvent("submit");}).delay(50);</script>';
						} else {
							echo '<h1>Error copying</h1>Could not copy the release profile successfully.<br />';
						}
					} else {
						echo '<h1>Oh. Shit. Something\'s wrong.</h1>error creating source directory<br />';
					}
				} else {
					// grab our manifest:
					$files = json_decode(file_get_contents('./release_profile.json'),true);
					$updates = json_decode(file_get_contents('./updates.json'),true);
					$files = array_merge($files['blobs'],$updates);
					$filecount = count($files);
					$currentfile = 1;

					foreach ($files as $file => $hash) {
						if (!file_exists('./update/'.$file)) {
							$path = pathinfo($file);
							if (!is_dir('./update/'.$path['dirname'])) mkdir('./update/'.$path['dirname'],0755,true);
							if (determinedCopy('http://cashmusic.s3.amazonaws.com/releases/'.$_SESSION['release_id'].'/'.$file,'./update/'.$file)) {
								echo $source_message;
								if ($currentfile != $filecount) {
									echo '<form action="" method="post" id="nextstepform"><input type="hidden" name="installstage" id="installstageinput" value="2" /></form>';
									echo '<script type="text/javascript">showProgress(' . ceil(100 * ($currentfile / $filecount)) . ');(function(){document.id("nextstepform").fireEvent("submit");}).delay(60);</script>';
								} else {
									// we're done; remove the manifest file
									if (file_exists('./release_profile.json')) {
										unlink('./release_profile.json');
									}
									echo '<form action="" method="post" id="nextstepform"><input type="hidden" id="installstagefade" value="1" /><input type="hidden" name="installstage" id="installstageinput" value="3" /></form>';
									echo '<script type="text/javascript">hideProgress();(function(){document.id("nextstepform").fireEvent("submit");}).delay(250);</script>';
								}
								break;
							} else {
								echo '<h1>Oh. Shit. Something\'s wrong.</h1>error copying file: ' . $file . '<br />';
								break;
							}
						}
						$currentfile = ++$currentfile;
					}
				}
			} else {
				echo $source_message;
				echo '<form action="" method="post" id="nextstepform"><input type="hidden" name="installstage" id="installstageinput" value="2" /></form>';
				echo '<script type="text/javascript">showProgress(0);(function(){document.id("nextstepform").fireEvent("submit");}).delay(250);</script>';
			}
			break;
		case "3":
			/**
			 * MAKE IT ALL HAPPEN
			 *
			 * Edit and move files, remove the updated script, party again.
			*/

			$current_version = CASHRequest::$version;
			$upgrade_failure = false;

			$total_versions_upgraded = 0;

			while (is_file('./update/update/updatescripts/' . $current_version . '.php')) {
				include('./update/update/updatescripts/' . $current_version . '.php');
				if ($upgrade_failure) {
					break;
				} else {
					$current_version++;
					$total_versions_upgraded++;
				}
			}

			if (!$upgrade_failure) {
				if ($total_versions_upgraded) {
					// move source files into place DO NOT OVERWRITE SETTINGS, DUMMY!
					if (is_file($cash_root_location . '/cashmusic.php')) unlink($cash_root_location . '/cashmusic.php');
					if (is_file('./admin/controller.php')) unlink('./admin/controller.php');
					if (is_dir($cash_root_location . '/classes')) rrmdir($cash_root_location . '/classes');
					if (is_dir($cash_root_location . '/elements')) rrmdir($cash_root_location . '/elements');
					if (is_dir($cash_root_location . '/lib')) rrmdir($cash_root_location . '/lib');
					if (is_dir('./admin/assets')) rrmdir('./admin/assets');
					if (is_dir('./admin/classes')) rrmdir('./admin/classes');
					if (is_dir('./admin/components')) rrmdir('./admin/components');
					if (is_dir('./admin/ui')) rrmdir('./admin/ui');
					if (is_dir('./api/classes')) rrmdir('./api/classes');
					if (is_dir('./public')) rrmdir('./public');
					if (
						!rename('./update/framework/php/cashmusic.php', $cash_root_location . '/cashmusic.php') || 
						!rename('./update/framework/php/classes', $cash_root_location . '/classes') || 
						!rename('./update/framework/php/elements', $cash_root_location . '/elements') || 
						!rename('./update/framework/php/lib', $cash_root_location . '/lib') || 
						!rename('./update/interfaces/php/admin/controller.php', './admin/controller.php') || 
						!rename('./update/interfaces/php/admin/assets', './admin/assets') || 
						!rename('./update/interfaces/php/admin/classes', './admin/classes') || 
						!rename('./update/interfaces/php/admin/components', './admin/components') || 
						!rename('./update/interfaces/php/admin/ui', './admin/ui') || 
						!rename('./update/interfaces/php/api/classes', './api/classes') || 
						!rename('./update/interfaces/php/public', './public')
					) {
						echo '<h1>Oh. Shit. Something\'s wrong.</h1> <p>We couldn\'t move files into place. Please make sure you have write access in '
						. 'the directory you specified for the core.</p>';
						break;
					}

					// success message
					echo '<h1>All done.</h1><p>Okay. You\'re all up-to-date.<br /><br /><a href="./admin/">Back to the admin</a></p>';
					echo '<br /><br /><br /><br /><small class="altcopystyle fadedtext">Like last time, I\'m deleting myself. This is going to give me a complex.</small>';
				} else {
					// error message
				echo '<h1>No upgrades performed.</h1><p>Either you are current or we couldn\'t find updates. Your curent version is reported as: ' . $current_version . '</p>';
				}
			} else {
				// error message
				echo '<h1>Error converting database.</h1><p>There was a problem updating your database. SQLite databases have been restored and the upgrade has been cancelled. If you were using MySQL you should restore your backup.</p>';
			}

			// remove the installer and the remaining source directory
			if (is_dir('./update')) rrmdir('./update');
			if (is_file('./update.php')) unlink('./update.php');
			
			break;
		default:
			echo "<h1>Oh. Shit. Something's wrong.</h1><p>We ran into an error. Please make sure you have write permissions in this directory and try again.</p>";
	}
}

ob_end_flush(); 
?>	