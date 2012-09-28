<?php
/**
 * CASH Music Web Installer
 *
 * This single file acts as a simple HTML5/JS website, downloading and installing
 * the latest platform files, configuring the database, setting and environment
 * details, and ultimately removing itself and any extra source from the host
 * server. (I kind of feel like a jerk making it delete itself.)
 *
 * Usage: just upload and run.
 *
 * A NOTE ABOUT SECURITY:
 * This file deletes itself for a reason. It clearly opens up some vulnerabilities
 * were it to be a public-facing script. Rather than wrestle with sanitizing all 
 * input we've chosen to use PDO to sanitize the SQL and remove the file when it
 * has run its course to avoid additional file edits. 
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

if (!isset($_SESSION['release_id']) || isset($_GET['origin'])) {
	if(isset($_GET['origin'])) {
		$_SESSION['release_id'] = $_GET['origin'];
	} else {
		$_SESSION['release_id'] = 'stable';
	}
}

$cash_root_location = false;

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
	<title>Install / CASH Music</title> 
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
	.loginlink {font-weight:bold;font-size:2em;}

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
			<h1>Hello.</h1>
			<p>
				This is the installer for the CASH Music platform. It will first grab the latest working 
				version of the platform, install it, then configure the bits and settings. 
			</p><p>
				You'll only need to answer a couple questions, but please run this file in an empty 
				folder of it's own if you're installing next to a live site. If you have any questions 
				please see <a href="http://help.cashmusic.org/" target="_blank">help.cashmusic.org</a> for more.
			</p>
				xo,
				<h2><a href="http://cashmusic.org/">CASH Music</a></h2>
		
			<br /><br />
			<div class="nextstep">
				<div class="altcopystyle fadedtext" style="margin-bottom:6px;">Whenever you're ready:</div>
				<form action="" method="post" id="nextstepform">
					<input type="hidden" name="installstage" id="installstageinput" value="2" />
					<input type="hidden" id="installstagefade" value="1" />
					<input type="submit" class="button" value="Start the installing" />
				</form>
			</div>
		</div>
		
		<div id="progressspc"><p class="progressamount">0%</p><div id="progressbar"><p class="progressamount">0%</p></div></div>
	</div>

	<div id="footer">
		<p><b>&copy; 2011 CASH Music.</b> All our code is open-source. <a href="http://cashmusic.org/why/" style="margin-left:0;">Learn more</a>. <a href="http://help.cashmusic.org/">Get help</a> <a href="http://cashmusic.org/donate" class="donatelink"><b>Donate</b></a></p>
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
			 * TEST CAPABILITIES
			 *
			 * Rather than hunt through ini settings, etc we're going to perform a couple real-world
			 * tests to make sure the server can run the install successfully. If not we fail 
			 * gracefully, or at least early. Like I did in 7th grade Spanish.
			 *
			 * Possible Errors:
			 * 1. directory not empty or in-progress
			 * 2. no write access
			 * 3. no curl / fopen wrappers
			 * 4. no PDO support
			 * 5. no sqlite / sqlite 3 support
			*/
			$all_tests_pass = true;
			if (!isset($_SESSION['testshaverun'])) {
				$test_error_number = 0;
				$test_error_message = '';

				// this nested structure is kinda nutty, but you know...also fine / finite
	  			$total_files = scandir(dirname('.'));
	  			$total_file_count = count($total_files);
	  			if ($total_file_count > 3 && !file_exists('./release_profile.json')) {
	  				// 1. test for EITHER empty directory or in-progress install
					$all_tests_pass = false;
					$test_error_number = 1;
					$test_error_message = 'Please run this in an empty directory. I found extra '
										. 'files but no CASH manifest...looks like this folder is '
										. 'already in use. Detected ' . $total_file_count . ' files.';
	  			} else {
	  				if (!mkdir('./test',0755,true)) {
		  				$all_tests_pass = false;
						$test_error_number = 2;
						$test_error_message = "Can't create a directory. Kind of need to do that. Sorry.";
		  			} else {
		  				if (!determinedCopy('http://cashmusic.s3.amazonaws.com/releases/'.$_SESSION['release_id'].'/release_profile.json','./test/release_profile.json')) {
			  				$all_tests_pass = false;
							$test_error_number = 3;
							$test_error_message = "I'm trying to copy files down but it's not working. "
												. "This means I can't see the outside word.<br /><br />"
												. 'cURL is' . (function_exists('curl_init') ? '' : ' not') . ' installed.<br />'
												. 'fopen wrappers are' . (ini_get('allow_url_fopen') ? '' : ' not') . ' enabled.';
			  			} else {
			  				if (!class_exists(PDO)) {
				  				$all_tests_pass = false;
								$test_error_number = 4;
								$test_error_message = "Couldn't find PDO. This is a required component of PHP that "
													. 'is included by default in most builds of PHP — apparently it '
													. 'has been turned off.';
				  			} else {
				  				// connect to the new db...will create if not found
								try {
									$pdo = new PDO ('sqlite:' . dirname('.') . '/test/test.sqlite');
									$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
									chmod(dirname('.') . '/test/test.sqlite',0755);
									$pdo->exec('CREATE TABLE test (id INTEGER PRIMARY KEY, testint integer);');
								} catch (PDOException $e) {
									$all_tests_pass = false;
									$test_error_number = 5;
									$test_error_message = "Looks like there's no support for sqlite 3. "
														. 'The exact error message given was: <br /><br />'
														. $e->getMessage();
								}
				  			}
			  			}
		  			}
	  			}
	  			// clean up testing mess:
	  			$_SESSION['testshaverun'] = true;
				if (is_dir('./test')) {
					rrmdir('./test');
				}
	  		}

			if (!$all_tests_pass) {
				echo '<h1>Error #' . $test_error_number . ' </h1>';
				echo '<p>' . $test_error_message . '</p>';

			} else {
				/**
				 * INSTALL CURRENT SOURCE
				 *
				 * Don't even bother checking for git. Set up directories and hit s3 for files, 
				 * looped AJAX delay keeps things running smooth...
				*/
				$source_message = '<h1>Installing.</h1><p>Copying files. '
					. 'This should take a couple minutes.</p>'
					. '<div class="altcopystyle fadedtext" style="margin-bottom:6px;">Copying files:</div>';
				// as long as determinedCopy isn't spinning we can copy files from the repo
				if (!$_SESSION['copying']) {
					if (!file_exists('./release_profile.json')) {
						// create the directory structure: remove any existing source files and re-download
						// we'll make a proper update script later.
						if (is_dir('./source')) {
							rrmdir('./source');
						}
						if (is_dir('./admin')) {
							rrmdir('./admin');
						}
						if (is_dir('./api')) {
							rrmdir('./api');
						}
						if (is_dir('./public')) {
							rrmdir('./public');
						}
						if (mkdir('./source',0755,true)) {
							// get repo from github, strip unnecessary files and write manifest:
							if (determinedCopy('http://cashmusic.s3.amazonaws.com/releases/'.$_SESSION['release_id'].'/release_profile.json','./release_profile.json')) {
								echo $source_message;
								echo '<form action="" method="post" id="nextstepform"><input type="hidden" name="installstage" id="installstageinput" value="2" /></form>';
								echo '<script type="text/javascript">showProgress(0);(function(){document.id("nextstepform").fireEvent("submit");}).delay(50);</script>';
							}
						} else {
							echo '<h1>Oh. Shit. Something\'s wrong.</h1>error creating source directory<br />';
						}
					} else {
						// grab our manifest:
						$release_profile = json_decode(file_get_contents('./release_profile.json'),true);
						$files = $release_profile['blobs'];
						$filecount = count($files);
						$currentfile = 1;

						foreach ($files as $file => $hash) {
							if (!file_exists('./source/'.$file)) {
								$path = pathinfo($file);
								if (!is_dir('./source/'.$path['dirname'])) mkdir('./source/'.$path['dirname'],0755,true);
								if (determinedCopy('http://cashmusic.s3.amazonaws.com/releases/'.$_SESSION['release_id'].'/'.$file,'./source/'.$file)) {
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
									echo '<h1>Oh. Shit. Something\'s wrong.</h1>error copying file: ' . (string)$file . '<br />';
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
			}
			break;
		case "3":
			/**
			 * GET SETTINGS FROM USER
			 *
			 * Locations, MySQL, and email address
			*/
			$settings_message = '<h1>Settings.</h1><p>You don\'t want us to just start putting '
				. 'files all over the place, do you? The form is auto-loaded with our best guess at a '
				. 'location for the core files, but you can put them anywhere.</p> '
				. '<p>The core files should ideally be stored somewhere that isn\'t public, '
				. 'so if possible keep them outside the www root folder that holds your site files.</p> '
				. '<p>While you\'re at it, add an email address and password for the main admin account.'
				. ' (<b>Hint:</b> use a real email address so you can reset your password if need be.)</p>';
			if (is_dir('./source/')) {
				if (!$cash_root_location) {
					// if $cash_root_location is not set above make a directory above the root
					$cash_root_location = dirname($_SERVER['DOCUMENT_ROOT']) . '/cashmusic';
				}
				echo $settings_message;
				echo '<form action="" method="post" id="nextstepform"><input type="hidden" id="installstagefade" value="1" /><input type="hidden" name="installstage" id="installstageinput" value="4" /> '
				. '<h3>Install core files to:</h3><input type="text" name="frameworklocation" value="' . $cash_root_location . '" /> '
				. '<h3>Admin email account:</h3><input type="text" name="adminemailaccount" value="admin@' . $_SERVER['SERVER_NAME'] . '" /> '
				. '<h3>Admin password:</h3><input type="text" name="adminpassword" value="" /><br /><span class="fadedtext altcopystyle">Password shown to avoid typos, but will be stored with secure encryption.</span><br /> '
				. '<br /><br /><div class="altcopystyle fadedtext" style="margin-bottom:6px;">Alright then:</div><input type="submit" class="button" value="Set it all up" /></div> '
				. '</form>';
			} else {
				echo '<h1>Oh. Shit. Something\'s wrong.</h1> No source directory found.<br />';
			}
			break;
		case "4":
			/**
			 * MAKE IT ALL HAPPEN
			 *
			 * Edit and move files, write redirects, set up DBs, remove the installer script, party.
			*/
			
			$admin_dir = rtrim(dirname($_SERVER['REQUEST_URI']),'/') . '/admin';
			$api_dir = rtrim(dirname($_SERVER['REQUEST_URI']),'/') . '/api';

			$user_settings = array(
				'frameworklocation' => (string)$_POST['frameworklocation'],
				'adminemailaccount' => (string)$_POST['adminemailaccount'],
				'adminpassword' => (string)$_POST['adminpassword'],
				'systemsalt' => md5($user_settings['adminemailaccount'] . time())
			);

			if ($user_settings['frameworklocation']) {
				if (!is_dir($user_settings['frameworklocation'])) {
					if (!mkdir($user_settings['frameworklocation'],0755,true)) {
						echo "<h1>Oh. Shit. Something's wrong.</h1><p>Couldn't create a directory at" . $user_settings['frameworklocation'] . ".</p>";
						break;
					}
				} else {
					if (is_dir($user_settings['frameworklocation'] . '/framework')) {
						rrmdir($user_settings['frameworklocation'] . '/framework');
						//echo 'removed old framework directory at ' . $cash_root_location . '/framework' . '<br />';
					}
				}
			} else {
				echo "<h1>Oh. Shit. Something's wrong.</h1><p>No core location specified.</p>";
				break;
			}
		
			// modify settings files
			if (
				!findReplaceInFile('./source/interfaces/php/admin/.htaccess','RewriteBase /interfaces/php/admin','RewriteBase ' . $admin_dir) || 
				!findReplaceInFile('./source/interfaces/php/api/.htaccess','RewriteBase /interfaces/php/api','RewriteBase ' . $api_dir) || 
				
				!findReplaceInFile('./source/interfaces/php/public/constants.php','$cashmusic_root = $root . "/../../../framework/php/cashmusic.php','$cashmusic_root = "' . $user_settings['frameworklocation'] . '/framework/cashmusic.php') || 
				
				!findReplaceInFile('./source/interfaces/php/admin/constants.php','$cashmusic_root = $root . "/../../../framework/php/cashmusic.php','$cashmusic_root = "' . $user_settings['frameworklocation'] . '/framework/cashmusic.php') || 
				!findReplaceInFile('./source/interfaces/php/admin/constants.php','define(\'ADMIN_WWW_BASE_PATH\', \'/interfaces/php/admin','define(\'ADMIN_WWW_BASE_PATH\', \'' . $admin_dir) || 
				
				!findReplaceInFile('./source/framework/php/settings/cashmusic_template.ini.php','driver = "mysql','driver = "sqlite') || 
				!findReplaceInFile('./source/framework/php/settings/cashmusic_template.ini.php','database = "cashmusic','database = "cashmusic.sqlite') || 
				!findReplaceInFile('./source/framework/php/settings/cashmusic_template.ini.php','apilocation = "http://localhost:8888/interfaces/php/api/','apilocation = "' . getBaseURL() . str_replace('/admin', '/api', $admin_dir)) || 
				!findReplaceInFile('./source/framework/php/settings/cashmusic_template.ini.php','salt = "I was born of sun beams; Warming up our limbs','salt = "' . $user_settings['systemsalt']) ||
				!findReplaceInFile('./source/framework/php/settings/cashmusic_template.ini.php','systememail = "info@cashmusic.org','systememail = "system@' . $_SERVER['SERVER_NAME'])
			) {
				echo "<h1>Oh. Shit. Something's wrong.</h1><p>We had trouble editing a few files. Please try again.</p>";
				break;
			}

			// move source files into place
			if (
				!rename('./source/framework/php/settings/cashmusic_template.ini.php', './source/framework/php/settings/cashmusic.ini.php') ||
				!rename('./source/framework/php', $user_settings['frameworklocation'] . '/framework') || 
				!rename('./source/interfaces/php/admin', './admin') || 
				!rename('./source/interfaces/php/api', './api') || 
				!rename('./source/interfaces/php/public', './public')
			) {
				echo '<h1>Oh. Shit. Something\'s wrong.</h1> <p>We couldn\'t move files into place. Please make sure you have write access in '
				. 'the directory you specified for the core.</p>';
				break;
			}
			
			// if the directory was never created then create it now
			if (!file_exists($user_settings['frameworklocation'] . '/db')) {
				mkdir($user_settings['frameworklocation'] . '/db',0755,true);
			} else {
				// blow away the old sqlite file. 
				if (file_exists($user_settings['frameworklocation'] . '/db/cashmusic.sqlite')) {
					unlink($user_settings['frameworklocation'] . '/db/cashmusic.sqlite');
				}
			}
			
			// connect to the new db...will create if not found
			try {
				$pdo = new PDO ('sqlite:' . $user_settings['frameworklocation'] . '/db/cashmusic.sqlite');
				$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			} catch (PDOException $e) {
				echo '<h1>Oh. Shit. Something\'s wrong.</h1> <p>Couldn\'t connect to the database.</p>';
				die();
				break;
			}

			if ($pdo) {
				chmod($user_settings['frameworklocation'] . '/db',0755);
				chmod($user_settings['frameworklocation'] . '/db/cashmusic.sqlite',0755);
			}

			// push in all the tables
			try {
				$pdo->exec(file_get_contents($user_settings['frameworklocation'] . '/framework/settings/sql/cashmusic_db_sqlite.sql'));
			} catch (PDOException $e) {
				echo '<h1>Oh. Shit. Something\'s wrong.</h1> <p>Couldn\'t create database tables. Files are all in-place, so you can manually edit settings or start over.';
				die();
				break;
			}

			if (!defined('CRYPT_BLOWFISH')) define('CRYPT_BLOWFISH', 0);
			if (!defined('CRYPT_SHA512')) define('CRYPT_SHA512', 0);
			if (!defined('CRYPT_SHA256')) define('CRYPT_SHA256', 0);

			if (CRYPT_BLOWFISH + CRYPT_SHA512 + CRYPT_SHA256) {
				if (CRYPT_BLOWFISH == 1) {
					$password_hash = crypt(md5($user_settings['adminpassword'] . $user_settings['systemsalt']), '$2a$13$' . md5(time() . $user_settings['systemsalt']) . '$');
				} else if (CRYPT_SHA512 == 1) {
					$password_hash = crypt(md5($user_settings['adminpassword'] . $user_settings['systemsalt']), '$6$rounds=6666$' . md5(time() . $user_settings['systemsalt']) . '$');
				} else if (CRYPT_SHA256 == 1) {
					$password_hash = crypt(md5($user_settings['adminpassword'] . $user_settings['systemsalt']), '$5$rounds=6666$' . md5(time() . $user_settings['systemsalt']) . '$');
				}
			} else {
				$key = time();
				$password_hash = $key . '$' . hash_hmac('sha256', md5($user_settings['adminpassword'] . $user_settings['systemsalt']), $key);
			}

			$data = array(
				'email_address' => $user_settings['adminemailaccount'],
				'password'      => $password_hash,
				'is_admin'      => true,
				'api_key'       => $api_key = hash_hmac('md5', time() . $password_hash . rand(976654,1234567267), $user_settings['systemsalt']) . substr((string) time(),6),
				'api_secret'    => hash_hmac('sha256', time() . $password_hash . rand(976654,1234567267), $user_settings['systemsalt']),
				'creation_date' => time()
			);
			$query = "INSERT INTO people (email_address,password,is_admin,api_key,api_secret,creation_date) VALUES (:email_address,:password,:is_admin,:api_key,:api_secret,:creation_date)";

			try {
				$q = $pdo->prepare($query);
			} catch (PDOException $e) {
				echo '<h1>Oh. Shit. Something\'s wrong.</h1> <p>Couldn\'t add the user to the database.</p>';
				die();
				break;
			}

			try {
				$success = $q->execute($data);
			} catch(PDOException $e) {
				echo '<h1>Oh. Shit. Something\'s wrong.</h1> <p>Couldn\'t add the user to the database.</p>';
				die();
				break;
			}

			// success message
			echo '<h1>All done.</h1><p>Okay. Everything is set up, configured, and ready to go. Follow the link below and login with your email.</p>'
			. '<p><br /><br /><a href="./admin/" class="loginlink">Click to login</a><br />';
			
			echo '<br /><br /><br /><br /><small class="altcopystyle fadedtext">I feel compelled to point out that in the time it took you to read this, I, your helpful installer script, have deleted '
			. 'myself in the name of security. It is a far, far better thing that I do, than I have ever done; it is a far, far better rest that I go to, than I '
			. 'have ever known.</small>';
			
			// create an index.php with a redirect to ./admin in the current directory, 
			// remove the installer and the remaining source directory
			if (is_dir('./source')) rrmdir('./source');
			if (is_file('./index.php')) unlink('./index.php');
			@file_put_contents('./index.php',"<?php header('Location: ./admin/'); ?>");
			if (is_file('./cashmusic_web_installer.php')) unlink('./cashmusic_web_installer.php');
			
			break;
		default:
			echo "<h1>Oh. Shit. Something's wrong.</h1><p>We ran into an error. Please make sure you have write permissions in this directory and try again.</p>";
	}
}

ob_end_flush(); 
?>
