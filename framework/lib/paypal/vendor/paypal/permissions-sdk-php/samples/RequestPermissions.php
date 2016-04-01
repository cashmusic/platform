<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>PayPal Permissions - Request Permissions</title>

</head>
<body>
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
<div id="request_form">

<form name="Form1" id="Form1" method="post"
	action="RequestPermissionsReceipt.php">
<center>
<h3>Permissions - Request Permissions</h3>
<table>
	<tr>
		<td class="thinfield" width="120">Scope</td>
		<td align="left"><input type=checkbox name=chkScope[]
			value='EXPRESS_CHECKOUT' /> EXPRESS_CHECKOUT <br />
		<input type=checkbox name=chkScope[] value='DIRECT_PAYMENT' />
		DIRECT_PAYMENT <br />
		<input type=checkbox name=chkScope[] value='AUTH_CAPTURE' />
		AUTH_CAPTURE <br />
		<input type=checkbox name=chkScope[] value='AIR_TRAVEL' /> AIR_TRAVEL
		<br />
		<input type=checkbox name=chkScope[] value='TRANSACTION_SEARCH' />
		TRANSACTION_SEARCH <br />
		<input type=checkbox name=chkScope[] value='RECURRING_PAYMENTS' />
		RECURRING_PAYMENTS <br />
		<input type=checkbox name=chkScope[] value='ACCOUNT_BALANCE' />
		ACCOUNT_BALANCE <br />
		<input type=checkbox name=chkScope[]
			value='ENCRYPTED_WEBSITE_PAYMENTS' /> ENCRYPTED_WEBSITE_PAYMENTS <br />
		<input type=checkbox name=chkScope[] value='REFUND' /> REFUND <br />
		<input type=checkbox name=chkScope[] value='BILLING_AGREEMENT' />
		BILLING_AGREEMENT <br />
		<input type=checkbox name=chkScope[] value='REFERENCE_TRANSACTION' />
		REFERENCE_TRANSACTION <br />
		<input type=checkbox name=chkScope[] value='MASS_PAY' /> MASS_PAY <br />
		<input type=checkbox name=chkScope[] value='TRANSACTION_DETAILS' />
		TRANSACTION_DETAILS <br />
		<input type=checkbox name=chkScope[] value='NON_REFERENCED_CREDIT' />
		NON_REFERENCED_CREDIT <br />
		<input type=checkbox name=chkScope[] value='SETTLEMENT_CONSOLIDATION' />
		SETTLEMENT_CONSOLIDATION <br />
		<input type=checkbox name=chkScope[] value='SETTLEMENT_REPORTING' />
		SETTLEMENT_REPORTING <br />
		<input type=checkbox name=chkScope[] value='BUTTON_MANAGER' />
		BUTTON_MANAGER <br />
		<input type=checkbox name=chkScope[]
			value='MANAGE_PENDING_TRANSACTION_STATUS' />
		MANAGE_PENDING_TRANSACTION_STATUS <br />
		<input type=checkbox name=chkScope[] value='RECURRING_PAYMENT_REPORT' />
		RECURRING_PAYMENT_REPORT <br />
		<input type=checkbox name=chkScope[]
			value='EXTENDED_PRO_PROCESSING_REPORT' />
		EXTENDED_PRO_PROCESSING_REPORT <br />
		<input type=checkbox name=chkScope[]
			value='EXCEPTION_PROCESSING_REPORT' /> EXCEPTION_PROCESSING_REPORT <br />
		<input type=checkbox name=chkScope[]
			value='ACCOUNT_MANAGEMENT_PERMISSION' />
		ACCOUNT_MANAGEMENT_PERMISSION <br />
		<input type=checkbox name=chkScope[] value='INVOICING' /> INVOICING <br />
		<input type=checkbox name=chkScope[] value='ACCESS_BASIC_PERSONAL_DATA' /> ACCESS_BASIC_PERSONAL_DATA <br />
		<input type=checkbox name=chkScope[] value='ACCESS_ADVANCED_PERSONAL_DATA' /> ACCESS_ADVANCED_PERSONAL_DATA <br />
		</td>
	</tr>
	<tr align="center">
		<td colspan="2"><br />
		<input type="submit" name="Submit" value="submit" /></td>
	</tr>

</table>
</center>
</form>
<a href="index.php" >Home</a>
</div>

</body>
</html>
