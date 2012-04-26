<?php $user_details = $cash_admin->getStoredResponse('userdetails',true); ?>
<div class="col_oneoftwo">
	<h3>Your email address</h3>
	<?php
	if (isset($_POST['doemailchange'])) {
		echo '<p class="highlightcopy">' . $post_message . '</p>';
	}
	?>
	<p>Change the email address associated with this account. (Note: enter a new address and your current password. The new email address must not be associated with another user already.)
	<form method="post" action="">
		<input type="hidden" name="doemailchange" value="makeitso" />
		<input type="hidden" name="email_address" value="<?php echo $user_details['email_address']; ?>" />
		<label for="new_email_address">Email</label><br />
		<input type="text" id="new_email_address" name="new_email_address" placeholder="<?php echo $user_details['email_address']; ?>" />
		<div class="row_seperator">.</div>
		<label for="password">Password</label><br />
		<input type="password" id="password" name="password" />
		<div class="row_seperator">.</div>
		<input class="button" type="submit" value="Change email address" />
	</form>
</div>
<div class="col_oneoftwo lastcol">
	<h3>Your password</h3>
	<?php
	if (isset($_POST['dopasswordchange'])) {
		echo '<p class="highlightcopy">' . $post_message . '</p>';
	}
	?>
	<p>To change your password you'll need to verify your current password and specify a new one.
		(All passwords are encrypted.)
	<form method="post" action="">
		<input type="hidden" name="dopasswordchange" value="makeitso" />
		<input type="hidden" name="email_address" value="<?php echo $user_details['email_address']; ?>" />
		<label for="password">Current password</label><br />
		<input type="password" id="password" name="password" />
		<div class="row_seperator">.</div>
		<label for="new_password">New password (Plain text for your review)</label><br />
		<input type="text" id="new_password" name="new_password" autocomplete="off" />
		<div class="row_seperator">.</div>
		<input class="button" type="submit" value="Change password" />
	</form>
</div>

<div class="row_seperator tall">.</div><br />
<div>
	<h3>API Key / Secret</h3>
	<p>
		API details for developer access:
	</p>
	<code>
	<b>API endpoint:</b> <?php echo CASH_API_URL; ?><br /><br />
	<b>Key:</b> <?php echo $user_details['api_key']; ?> &nbsp; &nbsp; <b>Secret:</b> 
		<?php if (isset($_GET['reveal'])) {
			echo $user_details['api_secret'];
		} else {
			echo '<a href="?reveal=true">Click to reveal API Secret</a>';
		}
		?>
	</code>
	
</div>