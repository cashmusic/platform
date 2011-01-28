<?php
/**
 * Add/manage transaction data
 *
 * @package seed.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class TransactionSeed {
	protected $dbseed;

	public function __construct($dbseed) {
		$this->dbseed = $dbseed;
	}

	public function addTransaction(
		$order_timestamp,
		$payer_email,
		$payer_id,
		$payer_firstname,
		$payer_lastname,
		$country,
		$product_sku,
		$product_name,
		$transaction_id,
		$transaction_status,
		$transaction_currency,
		$transaction_amount,
		$transaction_fee,
		$is_fulfilled,
		$referral_code,
		$nvp_request_json,
		$nvp_response_json,
		$nvp_details_json
	) {
		// hit each string argument with mysql_real_escape_string, add quotes
			$order_timestamp = "'" . mysql_real_escape_string($order_timestamp) . "'";
			$payer_email = "'" . mysql_real_escape_string($payer_email) . "'";
			$payer_id = "'" . mysql_real_escape_string($payer_id) . "'";
			$payer_firstname = "'" . mysql_real_escape_string($payer_firstname) . "'";
			$payer_lastname = "'" . mysql_real_escape_string($payer_lastname) . "'";
			$country = "'" . mysql_real_escape_string($country) . "'";
			$product_sku = "'" . mysql_real_escape_string($product_sku) . "'";
			$product_name = "'" . mysql_real_escape_string($product_name) . "'";
			$transaction_id = "'" . mysql_real_escape_string($transaction_id) . "'";
			$transaction_status = "'" . mysql_real_escape_string($transaction_status) . "'";
			$transaction_currency = "'" . mysql_real_escape_string($transaction_currency) . "'";
			$referral_code = "'" . mysql_real_escape_string($referral_code) . "'";
			$nvp_request_json = "'" . mysql_real_escape_string($nvp_request_json) . "'";
			$nvp_response_json = "'" . mysql_real_escape_string($nvp_response_json) . "'";
			$nvp_details_json = "'" . mysql_real_escape_string($nvp_details_json) . "'";

		$creation_date = time();
		$query = "INSERT INTO cmrc_transactions (order_timestamp,payer_email,payer_id,payer_firstname,payer_lastname,country,product_sku,product_name,transaction_id,transaction_status,transaction_currency,transaction_amount,transaction_fee,is_fulfilled,referral_code,nvp_request_json,nvp_response_json,nvp_details_json,creation_date) VALUES ($order_timestamp,$payer_email,$payer_id,$payer_firstname,$payer_lastname,$country,$product_sku,$product_name,$transaction_id,$transaction_status,$transaction_currency,$transaction_amount,$transaction_fee,$is_fulfilled,$referral_code,$nvp_request_json,$nvp_response_json,$nvp_details_json,$creation_date)";
		if ($this->dbseed->doQuery($query)) { 
			return true;
		} else {
			return false;
		}
	}
} // END class 
?>