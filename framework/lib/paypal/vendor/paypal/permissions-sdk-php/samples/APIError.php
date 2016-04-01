<?php
/*************************************************
 APIError.php
Displays error parameters.
*************************************************/
session_start();
$response=$_SESSION['reshash'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<body>
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
	<div id="request_form">
		<h3>
			<b>The PayPal API has returned an error!</b>
		</h3>
		<table width="280">

			<?php  //it will print if any URL errors
if(isset($_SESSION['curl_error_no'])) {
	$errorCode= $_SESSION['curl_error_no'] ;
	$errorMessage=$_SESSION['curl_error_msg'] ;

	?>


			<tr>
				<td class="thinfield">Error Number:</td>
				<td><?php $errorCode ?></td>
			</tr>
			<tr>
				<td>Error Message:</td>
				<td><?php $errorMessage ?></td>
			</tr>


		</table>
		<?php } else {

			/* If there is no URL Errors, Construct the HTML page with
			 Response Error parameters.
			*/
			?>
		<font size=2 color=black face=Verdana><b></b> </font> <b> PayPal API
			Error</b><br></br>

		<table width=400>
			<?php

			require 'ShowAllResponse.php';
			?>
		</table>
		<?php
		}// end else
		session_unset();
		?>
	</div>
</body>
</html>
