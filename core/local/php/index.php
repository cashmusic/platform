<?php
	include('logic/prep.php');
	include('logic/paypal.php');
	include('logic/emaillist.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Give Support / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="alternate" type="application/rss+xml" title="CASH Music blog RSS Feed" href="http://feeds.feedburner.com/cashmusic" /> 
<meta name="description" content="CASH Music is a nonprofit music tech foundation. Help them here." />
<meta name="keywords" content="artists art copyleft open community free music musicians code download alternative" />
<link rel="image_src" href="http://fbconnect.cashmusic.org/assets/images/fb_share_image.jpg" /> 

<link href="/assets/css/main.css" rel="stylesheet" type="text/css" />
<link rel="icon" type="image/png" href="/assets/images/icons/cash.png" />

<!--[if lt IE 7]>
        <script type="text/javascript" src="/assets/scripts/unitpngfix.js"></script>
<![endif]-->
</head>

<body>

<div id="wrap">

	<div id="cashstatementspc" style="position:relative;z-index:90;">
		<div id="cash_sitelogo"><a href="/"><img src="/assets/images/cash.png" alt="CASH Music" width="30" height="30" /></a></div>
		<div id="mainmenu">
			<a href="/about/">About</a>
			<a href="http://blog.cashmusic.org/">Blog</a>
			<a href="/showcase/">Showcase</a>
			<a href="/donate/">Give Support</a>
			<a href="/tools/">Download</a>
			<a href="http://help.cashmusic.org/">Get Help</a>
		</div>

		<div id="cashstatement">
			<?php
				if (isset($_SESSION['seed_state_emaillist'])) {
					if ($_SESSION['seed_state_emaillist'] == 'completed') {
						echo "<h1>Okay, you're on the list. Thanks for signing up!</h1>";
					} else {
						echo "<h1>Something went wrong in there. Try again? Please?</h1>";
					}
					unset($_SESSION['seed_state_emaillist']);
				} else if (isset($_SESSION['seed_state_payment'])) {
					if ($_SESSION['seed_state_payment'] == 'completed') {
						echo "<h1>Thank you!</h1>";
					} else if ($_SESSION['seed_state_payment'] == 'failed') {
						echo "<h1>There was a problem with your payment. Try again?</h1>";
					} else {
						echo "<h1>We're squirreling away some money and we need your help.</h1>";
					}
				} else {
					echo "<h1>We're squirreling away some money and we need your help.</h1>";
				}
			?> 
			<p class="openingp"></p>
			<?php
			if ($_SESSION['seed_error']) {
				echo "<p><span style=\"color:#f00;\">{$_SESSION['seed_error']}</span></p>";
			}
			?>
			<img src="/assets/images/squirrel.png" width="300" height="361" alt="squirrel." style="position:absolute;top:-80px;left:-230px;z-index:1;" />
		</div>
	</div>
	
	<div id="mainspc" style="z-index:100;">
		<?php
		session_start();
		switch ($_SESSION['seed_state_payment']) {
			case 'completed':
				?>
				
				<p>You can print this page as a receipt of your donation if you'd like...payment details are included below.</p>
				
				<div class="oneoftwo firstcol">
					<small>
						<b>Payment Details:</b><br />
						Donation from: <?php echo urldecode($_SESSION['seed_details']['EMAIL']); ?><br />
						Timestamp: <?php echo urldecode($_SESSION['seed_details']['TIMESTAMP']); ?><br />
						Amount: <?php echo urldecode($_SESSION['seed_details']['PAYMENTREQUEST_0_AMT']); ?>
						<br /><br />
						<span class="notation"> 
							CASH Music, Incorporated is a registered non-profit in Rhode Island, and has pending 501(c)3 tax-exempt status.
							Our EIN is 26-3804037.
						</span>
					</small>
				</div>
				<div class="oneoftwo lastcol">
					<form method="post" action="#"> 
					<input type="text" name="seed_email" value="" style="width:18em;" />
					<input type="hidden" name="seed_emaillist" value="go" /> 
					<input type="hidden" name="seed_listid" value="1" /> 
					<input type="submit" value="sign me up" class="button" /><br />  
					</form> 
					<span class="notation"> 
					We won't share, sell, or be jerks with your email address. 
					</span>
				</div>
				
				<?php
				// once we're done flush session data
				$_SESSION = array();
				session_destroy();
				session_write_close();
		        break;
		    case 'failed':
				echo '<p>Sorry, the payment failed. Please check your login and payment source and <a href="./?seed_payment=go">try again</a>.</p>';
		        break;
		    case 'uncompleted':
				echo '<p>Sorry, your transaction could not be completed. Please <a href="./?seed_payment=go">try again</a>.</p>';
				break;
			default:
				?>
				<p> 
					CASH Music is a nonprofit organization, meaning we exist to serve our mission of sustainability for artists and music.
					We believe that establishing a neutral organization focussed on providing music technology through open code is vital
					to the long-term health of the new music industry. To do that we need your help.
				</p><p> 
					Please consider making a donation.<br /><br />
				</p>
				
				<div class="oneoffour firstcol callout">
				<a href="./?seed_payment=go&amp;seed_sku=donation&amp;seed_addtoamt=25">$25</a>
				</div>
				<div class="oneoffour callout">
				<a href="./?seed_payment=go&amp;seed_sku=donation&amp;seed_addtoamt=50">$50</a>
				</div>
				<div class="oneoffour callout altcolor">
				<a href="./?seed_payment=go&amp;seed_sku=donation&amp;seed_addtoamt=100">$100</a>
				</div>
				<div class="oneoffour lastcol callout">
				<a href="./?seed_payment=go&amp;seed_sku=donation&amp;seed_addtoamt=500">$500</a>
				</div>
				
				<div class="clearfix">.</div> 
				
				<div class="oneoftwo firstcol">
					<div id="supportformspc">
						<small>Or any amount that you'd like:</small><br /><br />
						<form method="post" action="#"> 
						<input type="text" name="seed_addtoamt" value="5.00" style="width:5em;" />
						<input type="hidden" name="seed_payment" value="go" /> 
						<input type="hidden" name="seed_sku" value="donation" /> 
						<input type="submit" value="contribute" class="button" /><br /> 
						<img src="/assets/images/paypal_logo_b.png" width="38" height="12" alt="paypal" /> 
						</form> 
						<span class="notation"> 
						CASH Music is a registered non-profit in Rhode Island, and has pending 501(c)3 tax-exempt status.
						</span>
						
						<div style="margin-top:40px;line-height:30px;" class="nofx">
							<a href="http://twitter.com/share" class="twitter-share-button nofx" data-url="http://cashmusic.org/donate/" data-text="Help @cashmusic change the music industry." data-count="horizontal">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script><br />
							<a name="fb_share" type="button_count" share_url="http://cashmusic.org/donate/" href="http://www.facebook.com/sharer.php" class="nofx">Share</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script><br />
							<script type="text/javascript" src="http://widgets.digg.com/buttons.js"></script><a class="DiggThisButton DiggCompact nofx"></a>
						</div>
					</div>
				</div>
				
				<div class="oneoftwo lastcol" style="padding-top:8.5em;">
					<h2>Others Who've Helped:</h2>
					<?php

					/**
					 * Get either a Gravatar URL or complete image tag for a specified email address.
					 *
					 * @param string $email The email address
					 * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
					 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
					 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
					 * @param boole $img True to return a complete IMG tag False for just the URL
					 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
					 * @return String containing either just a URL or a complete image tag
					 * @source http://gravatar.com/site/implement/images/php/
					 */
					function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
						$url = 'http://www.gravatar.com/avatar/';
						$url .= md5( strtolower( trim( $email ) ) );
						$url .= "?s=$s&d=$d&r=$r";
						if ( $img ) {
							$url = '<img src="' . $url . '"';
							foreach ( $atts as $key => $val )
								$url .= ' ' . $key . '="' . $val . '"';
							$url .= ' />';
						}
						return $url;
					}

					echo get_gravatar('jesse@cashmusic.org',40,'mm','pg',true,array('class'=>'gravatar'));

					?>
					
					<br /><br /><small><b>Mailing List</b><br />You can also help by spreading the word, so let's stay in touch:</small><br /><br />
					<form method="post" action="#"> 
					<input type="text" name="seed_email" value="" style="width:18em;" />
					<input type="hidden" name="seed_emaillist" value="go" /> 
					<input type="hidden" name="seed_listid" value="1" /> 
					<input type="submit" value="sign me up" class="button" /><br />  
					</form> 
					<span class="notation"> 
					We won't share, sell, or be jerks with your email address. 
					</span>
				</div>
				
				<div class="clearfix">.</div> 
				
				<?php
		}
		?>
	</div>
</div>

<div id="footer" style="z-index:200;"> 
	<div id="mainfooter">
		<h2>CASH Music</h2>
		<div id="footercontactspc" class="toprow">
			<p>
			<b>Contact Us</b><br />
			<a href="mailto:info@cashmusic.org">info@cashmusic.org</a>
			<br /><br />	
			CASH Music<br />
			PO Box 3782<br /> 
			Newport, RI<br />
			02840
			</p>
		</div>
		<div id="footerdontatespc" class="toprow">
			<p>
			<b>Please Consider Giving</b><br />
			Donations are greatly appreciated and directly support development.  
			</p>
			<form method="post" action="http://cashmusic.org/beginpayment.php">
			<input type="text" name="a" value="5.00" style="width:5em;" /> 
			<input type="hidden" name="w" value="donation" />
			<input type="submit" value="contribute" class="button" /><br />
			<img src="/assets/images/paypal_logo_w.png" width="38" height="12" alt="paypal" />
			</form>
			<p class="darker">
			CASH Music is a registered non-profit in Rhode Island, and has pending 501(c)3 tax-exempt status.
			</p>
		</div>
		
		
		<div>
			<p><b>Visit Us Elsewhere</b><br /><br /></p>
			<div id="socialtwitter" class="sociallink"><a href="http://twitter.com/cashmusic"><img src="/assets/images/clear.gif" alt="twitter" /></a></div>
			<div id="socialfacebook" class="sociallink"><a href="http://facebook.com/cashmusic.org"><img src="/assets/images/clear.gif" alt="facebook" /></a></div>
			<div id="socialgithub" class="sociallink"><a href="http://github.com/cashmusic"><img src="/assets/images/clear.gif" alt="github" /></a></div>
			<div id="sociallighthouse" class="sociallink"><a href="http://cashmusic.lighthouseapp.com/"><img src="/assets/images/clear.gif" alt="lighthouse" /></a></div>
		</div>
	</div>
	
	<p id="copyrightspc" class="darker"><b>&copy; 2010 CASH Music.</b> Words and images released under a <a href="http://creativecommons.org/licenses/by/3.0/" class="darker">Creative Commons BY license</a>. All our code is open-source. <a href="/about/faq/">Learn more</a>.</p>
	
	<a href="/"><img src="/assets/images/cash404040.png" alt="CASH Music" width="30" height="30" id="footerlogo" /></a>
</div>

<script type="text/javascript">
//<![CDATA[ 
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
//]]> 
</script><script type="text/javascript">
//<![CDATA[ 
try {var pageTracker = _gat._getTracker("UA-7451645-1");pageTracker._trackPageview();} catch(err) {}
//]]> 
</script>
</body>

</html>