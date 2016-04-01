<?php
use PayPal\Service\PermissionsService;
use PayPal\Types\Common\RequestEnvelope;
use PayPal\Types\Perm\RequestPermissionsRequest;
/********************************************
 RequestPermissionsReceipt.php
Called by RequestPermissions.php

# RequestPermissions API
Use the RequestPermissions API operation to request permissions to execute API operations on a PayPal account holder's behalf.
This sample code uses Permissions PHP SDK to make API call
********************************************/
require_once('PPBootStrap.php');

$serverName = $_SERVER['SERVER_NAME'];
$serverPort = $_SERVER['SERVER_PORT'];
$url = dirname('http://'.$serverName.':'.$serverPort.$_SERVER['REQUEST_URI']);
$returnURL = $url."/GetAccessToken.php";
$cancelURL = $url. "/RequestPermissions.php";

/*
 *  ##RequestPermissionsRequest
`Scope`, which can take at least 1 of the following permission
categories:

* EXPRESS_CHECKOUT
* DIRECT_PAYMENT
* AUTH_CAPTURE
* AIR_TRAVEL
* TRANSACTION_SEARCH
* RECURRING_PAYMENTS
* ACCOUNT_BALANCE
* ENCRYPTED_WEBSITE_PAYMENTS
* REFUND
* BILLING_AGREEMENT
* REFERENCE_TRANSACTION
* MASS_PAY
* TRANSACTION_DETAILS
* NON_REFERENCED_CREDIT
* SETTLEMENT_CONSOLIDATION
* SETTLEMENT_REPORTING
* BUTTON_MANAGER
* MANAGE_PENDING_TRANSACTION_STATUS
* RECURRING_PAYMENT_REPORT
* EXTENDED_PRO_PROCESSING_REPORT
* EXCEPTION_PROCESSING_REPORT
* ACCOUNT_MANAGEMENT_PERMISSION
* INVOICING
* ACCESS_BASIC_PERSONAL_DATA
* ACCESS_ADVANCED_PERSONAL_DATA
*/
$scope = array();
if(isset($_POST['chkScope'])) {
	$i = 0;
	foreach ($_POST['chkScope'] as $value) {
		$scope[$i++] = $value;
	}
}
$requestEnvelope = new RequestEnvelope("en_US");

/*
 *  Create RequestPermissionsRequest object which takes mandatory params:

* `Scope`
* `Callback` - Your callback function that specifies actions to take
after the account holder grants or denies the request.
*/
$request = new RequestPermissionsRequest($scope, $returnURL);
$request->requestEnvelope = $requestEnvelope;

/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
*/
$service = new PermissionsService(Configuration::getAcctAndConfig());
try {
	/*
	 *  ## Making API call
	Invoke the appropriate method corresponding to API in service
	wrapper object
	*/
	$response = $service->RequestPermissions($request);
} catch(Exception $ex) {
	require 'Error.php';
	exit;
}
/* Display the API response back to the browser.
 If the response from PayPal was a success, display the response parameters'
If the response was an error, display the errors received using APIError.php.
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<body>
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
	<div id="request_form">
		<h3>RequestPermissions - Response</h3>
		<?php
		$token = $response->token;
		if(strtoupper($response->responseEnvelope->ack) == 'SUCCESS') {

	/*
	 * // ###Redirecting to PayPal
	// Once you get the success response, user needs to redirect to
	// paypal to authorize. Construct the `redirectUrl` as follows,
	// `redirectURL=https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_grant-permission&request_token="+$response->token;`
	// Once you are done with authorization, you will be returning
	// back to `callback` url mentioned in your request. While
	// returning, PayPal will send two parameters in request:
	//
	// * `request_token`
	// * `token_verifier`
	// You have to use these values in `GetAccessToken` API call to
	// generate `AccessToken` and `TokenSecret`

	// A token from PayPal that enables the request to obtain permissions.
	*/
	$payPalURL = 'https://www.sandbox.paypal.com/webscr&cmd='.'_grant-permission&request_token='.$token;
	echo "<table>";
	echo "<tr><td>Ack :</td><td><div id='Ack'>". $response->responseEnvelope->ack ."</div> </td></tr>";
	echo "<tr><td>Token :</td><td><div id='Token'>". $response->token ."</div> </td></tr>";
	echo "<tr><td><a href=$payPalURL><b>* Redirect URL to Complete RequestPermissions API operation </b></a></td></tr>";
	echo "</table>";
}
require_once 'ShowAllResponse.php';
?>

	</div>
</body>
</html>
