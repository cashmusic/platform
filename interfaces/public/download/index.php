<?php
// include the necessary bits, define the page directory
// Define constants too
$cashmusic_root = dirname(__FILE__) . "/../../../framework/cashmusic.php";

/*
if (!file_exists($cashmusic_root)) {
	$cash_settings = json_decode(getenv('cashmusic_platform_settings'),true);
	// env settings allow use on multi-server, multi-user instances
	if ($cash_settings) {
		// thanks to json_decode this will be null if the
		if (isset($cash_settings['platforminitlocation'])) {
			$cashmusic_root = $_SERVER['DOCUMENT_ROOT'] . $cash_settings['platforminitlocation'];
		}
	}
}
*/

define('CASH_PLATFORM_PATH', $cashmusic_root);
require_once(CASH_PLATFORM_PATH);

if (isset($_GET['code'])) {
	$redeemcode_request = new CASHRequest(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'redeemlockcode',
			'scope_table_alias' => 'assets',
			'code' => $_GET['code']
		)
	);
	if ($redeemcode_request->response['payload']) {
		$unlock_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset',
				'cash_action' => 'unlock',
				'id' => $redeemcode_request->response['payload']['scope_table_id']
			)
		);
		$redirect_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset',
				'cash_action' => 'claim',
				'id' => $redeemcode_request->response['payload']['scope_table_id']
			)
		);
	} else {
		echo 'That code is not valid or has already been used.';
	}
}
?>
