<?php 
/**
 *
 * The main page publishing script for a CASH Music instance. Handles the main 
 * public-facing pages, either the default service page or the user-published 
 * pages (assumes user id = 1 for single-user instances, looks for a 'username')
 * GET parameter for multi-user instances.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2014, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/

/* SINGLE USER SUPPORT? UNCOMMENT
 * $user_id = 1; // we can assume 1 for single-user instances
 */

$user_id = false;
// if we've got a username we need to find the id — over-write no matter what. no fallback to user id 1
if (isset($_GET['subdomain']) || isset($_GET['path'])) {

	require_once(__DIR__ . '/admin/constants.php');
	//error_log($_GET['subdomain'] . "\n" . $_GET['path'] . "\n" . print_r($_GET,true));

	if ($_GET['subdomain'] !== 'cashmusic.org' && 
		$_GET['subdomain'] !== 'x.cashmusic.org' && 
		$_GET['subdomain'] !== 'localhost.cashmusic.org' &&
		$_GET['subdomain'] !== 'testing.cashmusic.org' &&
		$_GET['subdomain'] !== 'staging.cashmusic.org' &&
		$_GET['subdomain'] !== 'air.cashmusic.org' && 
		SUBDOMAIN_USERNAMES
	) {
		$username = explode('.', $_GET['subdomain']);
		$username = $username[0];
	} else {
		$username = explode('/', trim($_GET['path'],'/'));
		$username = $username[0];
	}

	if ($username) {
		// include the necessary bits, define the page directory
		// Define constants too
		$page_vars = array(); // setting up the array for page variables
		$page_vars['www_path'] = ADMIN_WWW_BASE_PATH;
		$page_vars['jquery_url'] = (defined('JQUERY_URL')) ? JQUERY_URL : ADMIN_WWW_BASE_PATH . '/ui/default/assets/scripts/jquery-1.8.2.min.js';
		$page_vars['cdn_url'] = (defined('CDN_URL')) ? CDN_URL : ADMIN_WWW_BASE_PATH;

		// launch CASH Music
		require_once($cashmusic_root);

		if (stripos($username,'/')) {
			$username = explode('/', $username);
			$username = $username[0];
		}
		$user_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getuseridforusername',
				'username' => $username
			)
		);
		if ($user_request->response['payload']) {
			$user_id = $user_request->response['payload'];
		} else {
			$user_id = false;
		}
	}
}

 // error_log($user_id);

// if we find a user check for a template and render one if found.
if ($user_id) {
	$settings_request = new CASHRequest(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'getsettings',
			'type' => 'public_profile_template',
			'user_id' => $user_id
		)
	);
	if ($settings_request->response['payload']) {
		$template_id = $settings_request->response['payload'];
	} else {
		$template_id = false;
	}

	$template = false;
	if ($template_id) {
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'gettemplate',
				'template_id' => $template_id,
				'user_id' => $user_id
			)
		);
		$template = $template_request->response['payload'];
	}

	// with a real user but no template we redirect to the admin
	if ($template) {
		$element_embeds = false; // i know we don't technically need this, but the immaculate variable in preg_match_all freaks me out
		$found_elements = preg_match_all('/{{{element_(.*?)}}}/',$template,$element_embeds, PREG_PATTERN_ORDER);
		if ($found_elements) {

			foreach ($element_embeds[1] as $element_id) {
				ob_start();
				CASHSystem::embedElement($element_id);
				$page_vars['element_' . $element_id] = ob_get_contents();
				ob_end_clean();
			}
			
		}
		// render out the page itself
		echo CASHSystem::renderMustache($template,$page_vars);
		exit();
	} else {
		// redirect to the admin
		header('Location: ./admin/');
	}
} 


/***************************************
 *
 *  ADD PUBLIC PAGE BELOW CLOSING "?>"
 *
 ***************************************/
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>CASH Music</title>
		<meta name="description" content="The CASH Music platform is a set of digital tools designed to solve real problems for working musicians, based on years of direct collaboration with artists.">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!--<link rel="stylesheet" href="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/css/front.css">-->
		<link rel="stylesheet" href="admin/ui/default/assets/css/front.css">
		<link href='//fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
		<!--facebook metadata-->
		<meta property="og:title" content="CASH Music - A nonprofit organization focused on educating and empowering artists and their fans to foster a viable and sustainable future for music."/>
		<meta property="og:url" content="http://www.cashmusic.org"/>
		<meta property="og:site_name" content="www.cashmusic.org" />
		<meta property="fb:admins" content="100001809157387" />
		<!--facebook metadata-->
	</head>
	<body>
   <div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=137611429640196&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
		<!--[if lt IE 7]>
			<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
		<![endif]-->
<div class="menutoggle">Menu</div>
	<nav>
	<div class="social"><!--<iframe class="git" src="https://ghbtns.com/github-btn.html?user=cashmusic&repo=platform&type=fork"
  allowtransparency="true" frameborder="0" scrolling="0" width="62" height="20"></iframe>-->
<a href="https://github.com/cashmusic/platform" target="_blank" class="git">Fork Us On Github</a>
  <a href="https://twitter.com/cashmusic" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow @cashmusic</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script><div class="fb-like" data-href="https://www.facebook.com/cashmusic.org" data-width="75" data-layout="button" data-action="like" data-show-faces="true" data-share="false"></div></div><!--social-->
				<ul>
					<!--<li><a href="http://www.cashmusic.org">Home</a></li>-->
					<li><a href="http://about.cashmusic.org">About</a></li>
					<li><a href="https://medium.com/cash-music" target="_blank">Blog</a></li>
					<!--<li><a href="http://tools.cashmusic.org">Tools</a></li>
					<li><a href="http://learn.cashmusic.org">Learning</a></li>
					<li><a href="http://events.cashmusic.org">Events</a></li>-->
					<li><a href="http://donate.cashmusic.org">Donate</a></li>          	
				</ul>
			</nav>
		<div class="header-container">
			   <h1 class="logo"><img src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/images/invert_logo.svg" alt="CASH Music" /></h1>
		</div>

		<div class="main-container">
			<div class="main wrapper clearfix">
			<div class="panel first">
				<div class="inner">
				<h1>Get Started With CASH Music.</h1>
				<div class="examples">
				<!--<img src="images/examples/examples.gif" alt="examples"/> -->
			  
			   <!-- <iframe width="1280" height="720" src="//www.youtube.com/embed/YLS_WWYUQBg?&amp;vq=hd720&amp;modestbranding=1&amp;showinfo=0&amp;autoplay=1&amp;autohide=1&amp;color=white" autoplay="1" frameborder="0" allowfullscreen></iframe> -->

				<video autoplay loop>
				<source src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/video/phone.webm" type="video/webm">
				Sorry Your browser does not support the video tag.
				</video> 
				</div>


				<h2>Manage your mailing list, sell your music, organize your digital world. The CASH Music platform is free to use, now and forever.</h2> 
				 <div class="action">
				<a class="singup btn" href="/admin/dosignup/">Sign up</a><!--signup--> <a class="login btn" href="/admin/">Login</a><!--login--></div><!--action-->
				 </div><!--inner-->
		</div><!--panel-->	

		<div class="panel made">
			<div class="inner">
			<!--<img src="/admin/admin/ui/default/assets/images/phone.png" alt="phone"/>-->
				<h1>Made For Musicians.</h1>
				<video class="phonevid" autoplay loop>
				<source src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/video/phone.mp4" type="video/mp4">
				<source src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/video/phone.webm" type="video/webm">
				Sorry Your browser does not support the video tag.
				</video> 
				<h2>The CASH Music platform is a set of digital tools designed to solve real problems for working musicians, based on years of direct collaboration with artists.</h2>
			  
			</div><!--inner-->
		</div><!--panel-->	
		<div class="panel free">
		<img src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/images/free.jpg" alt="Free"/>
		<div class="inner">
				<h1>Free Forever.</h1>
				<h2>Our goal is to help build a sustainable future for music. Musicians are our partners, not our customers, and our platform will always be free to use.</h2>
			</div><!--inner-->
		</div><!--panel-->	
		<div class="panel better">
		<img src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/images/blank.gif" alt="Getting Better"/>
			<div class="inner">
				<h1>Getting Better All the Time.</h1>
				<h2>CASH Music is an open source project, with a community of brilliant developers working every day to make it better.</h2>
				</div><!--inner-->
				</div><!--panel-->	
				
		<div class="panel about">
			<div class="inner">
				<h1>About Us</h1>
				<h2>CASH Music is a <a href="https://medium.com/cash-music/the-organization-is-our-protest-song-1c9b01ea3ceb" target="_blank">nonprofit organization</a> focused on educating and empowering artists and their fans to foster a viable and sustainable future for music. We believe the best way to ensure a sustainable future for music is to invest in its creators.</h2>
			
			   
				<script type="text/javascript" src="https://air.cashmusic.org/public/cashmusic.js"></script><script type="text/javascript"> window.cashmusic.embed('https://air.cashmusic.org/public/','12');</script>

				<p>Made possible with the support of:</p>
				<a class="shuttleworth" href="http://www.shuttleworthfoundation.org/" target="_blank"><img src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/images/shuttleworth.png" alt="Shuttleworth Foundation"/></a>
				<a class="mozilla" href="https://www.mozilla.org/" target="_blank"><img src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/images/mozilla.png" alt="Mozilla"/></a>
				<a class="mailchimp" href="http://www.mailchimp.com/" target="_blank"><img src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/images/mailchimp.png" alt="Mailchimp"/></a>
				<a class="rackspace" href="http://www.rackspace.com/" target="_blank"><img src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/images/rackspace.png" alt="Rackspace"/></a>
				<a class="maxcdn" href="http://www.maxcdn.com/" target="_blank"><img src="https://www-cashmusic.netdna-ssl.com/admin/ui/default/assets/images/maxcdn.png" alt="Max CDN"/></a>
				</div><!--inner-->
			</div><!--panel-->	
		</div> <!-- #main -->
		</div> <!-- #main-container -->


		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script>
		$( document ).ready(function() {
			$( ".menutoggle" ).click(function() {

				if ( !$(".menutoggle").hasClass("show")){
				$(".menutoggle").addClass("show");
				$(".menutoggle").removeClass("hide");
				
				} else {

					if ( $(".menutoggle").hasClass("show")){
						$(".menutoggle").removeClass("show");
						$(".menutoggle").addClass("hide");
					}
				}
					$("body").toggleClass("show");
			});
		});

		// analytics
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-55083593-1', 'auto');
		ga('send', 'pageview');
		</script>
	</body>
</html>




