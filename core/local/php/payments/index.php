<?php
	include('logic/paypal.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Payment Test / CASH Music</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="alternate" type="application/rss+xml" title="CASH Music blog RSS Feed" href="http://feeds.feedburner.com/cashmusic" /> 
<meta name="description" content="CASH Music is a nonprofit music tech foundation." />
<meta name="keywords" content="artists art copyleft open community free music musicians code download alternative" />

<link href="/assets/css/main.css" rel="stylesheet" type="text/css" />
<link rel="icon" type="image/png" href="/assets/images/icons/cash.png" />

<!--[if lt IE 7]>
        <script type="text/javascript" src="/assets/scripts/unitpngfix.js"></script>
<![endif]-->
</head>

<body>

<div id="wrap">

	<div id="cashstatementspc">
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
			<h1>Payment Test</h1>
			<p>
				Running through basic Paypal API integration with S3 downloads.
			</p>
		</div>
	</div>
	
	<div id="mainspc">
		<?php
		session_start();
		switch ($_SESSION['seed_state']) {
			case 'completed':
				echo(
					'<b>Transaction details (Save to DB, list, etc.)</b><br />' .
					'timestamp: ' . urldecode($_SESSION['seed_response']['TIMESTAMP']) . '<br />' .
					'email: ' . urldecode($_SESSION['seed_response']['EMAIL']) . '<br />' .
					'payer id: ' . urldecode($_SESSION['seed_response']['PAYERID']) . '<br />' .
					'first name: ' . urldecode($_SESSION['seed_response']['FIRSTNAME']) . '<br />' .
					'last name: ' . urldecode($_SESSION['seed_response']['LASTNAME']) . '<br />' .
					'country: ' . urldecode($_SESSION['seed_response']['COUNTRYCODE']) . '<br />' .
					'currency: ' . urldecode($_SESSION['seed_response']['PAYMENTREQUEST_0_CURRENCYCODE']) . '<br />' .
					'amount: ' . urldecode($_SESSION['seed_response']['PAYMENTREQUEST_0_AMT']) . '<br />' .
					'transaction id: ' . urldecode($_SESSION['seed_response']['PAYMENTREQUEST_0_TRANSACTIONID']) . '<br /><br />' .
					'full response, JSON-encoded:<br />' . str_replace(',',', ',json_encode($_SESSION['seed_response']))
				);
				// once we're done flush session data
				$_SESSION = array();
				session_destroy();
				session_write_close();
		        break;
		    case 'failed':
				echo '<p>Sorry, the payment failed. Please check your login and payment source and <a href="./?seed_begin=go">try again</a>.</p>';
		        break;
		    case 'uncompleted':
				echo '<p>Sorry, your transaction could not be completed. Please <a href="./?seed_begin=go">try again</a>.</p>';
				break;
			default:
				?>
				<p>
					This link will initiate a $5 charge for 'stuff' to the Paypal sandbox server:
				</p>
				<a href="./?seed_begin=go">Begin Test</a><br />
				<small>$5 charge</small>
				
				<?php
		}
		?>
	</div>
</div>

<div id="footer"> 
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