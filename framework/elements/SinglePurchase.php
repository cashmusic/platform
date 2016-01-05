<?php
/**
 * Single Purchase element
 *
 * @package digitalpurchase.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2014, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class SinglePurchase extends ElementBase {
	public $type = 'singlepurchase';
	public $name = 'Single Purchase';

	public function getData() {
		// define $markup to store all screen output
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'getitem',
				'id' => $this->options['item_id']
			)
		);
		$item = $item_request->response['payload'];
		$this->element_data['item_name'] = $item['name'];
		$this->element_data['item_price'] = number_format($item['price'], 2, '.', '');
		$this->element_data['item_flexible_price'] = $item['flexible_price'];
		$this->element_data['item_description'] = $item['description'];
		$this->element_data['item_asset'] = $item['fulfillment_asset'];
		$this->element_data['item_asset'] = $item['fulfillment_asset'];
		$this->element_data['connection_type'] = CASHData::getConnectionType($this->element_data['connection_id']);

        // we need do do all this to get the publishable key, i believe
        $connection_settings = CASHSystem::getConnectionTypeSettings($this->element_data['connection_type']);
        $seed_class = $connection_settings['seed'];

        // we're going to switch seeds by $connection_type, so check to make sure this class even exists
        if (!class_exists($seed_class)) {
            $this->setErrorMessage("Couldn't find payment type {$this->element_data['connection_type']}.");
            return false;
        }

        // call the payment seed class
        $payment_seed = new $seed_class($this->element_data['user_id'],$this->element_data['connection_id']);
        $this->element_data['public_key'] = $payment_seed->publishable_key;

		if ($item['available_units'] != 0) {
			$this->element_data['is_available'] = true;
		} else {
			$this->element_data['is_available'] = false;
		}

		$currency_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'getsettings',
				'type' => 'use_currency',
				'user_id' => $this->element_data['user_id']
			)
		);
		if ($currency_request->response['payload']) {
			$this->element_data['currency'] = CASHSystem::getCurrencySymbol($currency_request->response['payload']);
		} else {
			$this->element_data['currency'] = CASHSystem::getCurrencySymbol('USD');
		}

		if (
			$this->status_uid == 'commerce_finalizepayment_200' || 
			$this->status_uid == 'element_redeemcode_200' ||
			$this->status_uid == 'commerce_initiatecheckout_200' && $this->original_response['payload'] == 'force_success'
			) {
			if ($item['fulfillment_asset'] != 0) {
				$fulfillment_request = new CASHRequest(
					array(
						'cash_request_type' => 'asset', 
						'cash_action' => 'getfulfillmentassets',
						'asset_details' => $item['fulfillment_asset']
					)
				);
				if ($fulfillment_request->response['payload']) {
					$this->element_data['fulfillment_assets'] = new ArrayIterator($fulfillment_request->response['payload']);
				}
			}
			$this->setTemplate('success');
		} elseif ($this->status_uid == 'commerce_initiatecheckout_400') {
			// could happen on a database glitch, but probably means the user set a pay-minimum price below the
			// minimum price. what a heel.
			$this->element_data['error_message'] = 'Make sure you enter a price of at least ' . $this->element_data['currency'] . $item['price'] . ' and try again.';
		} elseif ($this->status_uid == 'commerce_finalizepayment_400' || $this->status_uid == 'element_redeemcode_400') {
			// payerid is specific to paypal, so this is temporary to tell between canceled and errored:
			if (isset($_GET['PayerID'])) {
				//$this->element_data['error_message'] = $this->options['message_error'];
				$this->element_data['error_message'] = print_r($this->original_response,true);
			}
		} elseif (isset($_POST['singlepurchase1'])) {
			$total_price = $item['price'];
			if (isset($_POST['total_price'])) {
				$total_price = $_POST['total_price'];
			}
			$this->element_data['total_price'] = $total_price;
			if ($this->element_data['region1_cost'] + $this->element_data['region2_cost'] == 0.00) {
				$this->element_data['no_shipping'] = true;
			}
			if ($total_price >= $item['price']) {
				$this->setTemplate('shipping');
			} else {
				$this->element_data['error_message'] = 'Make sure you enter a price of at least ' . $this->element_data['currency'] . $item['price'] . ' and try again.';
			}
		}
		return $this->element_data;	
	}
} // END class 
?>