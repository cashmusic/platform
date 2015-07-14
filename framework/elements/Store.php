<?php
/**
 * Store element
 *
 * @package store.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2015, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class Store extends ElementBase {
	public $type = 'store';
	public $name = 'Store';

	public function getData() {
		// define $markup to store all screen output
		$items_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'getitemsforuser',
				'user_id' => $this->element_data['user_id']
			)
		);
		$items = $items_request->response['payload'];

		/*
		$this->element_data['item_name'] = $item['name'];
		$this->element_data['item_price'] = number_format($item['price'], 2, '.', '');
		$this->element_data['item_flexible_price'] = $item['flexible_price'];
		$this->element_data['item_description'] = $item['description'];
		$this->element_data['item_asset'] = $item['fulfillment_asset'];
		*/

		$featured_items_ids = array();
		$featured_items = array();
		$unfeatured_items = array();
		if (is_array($this->element_data['featured_items'])) {
			foreach ($this->element_data['featured_items'] as $i) {
				$featured_items_ids[] = $i['item_id'];
			}
		}

		foreach ($items as $i) {
			if (in_array($i['id'],$featured_items_ids)) {
				$featured_items[] = $i;
			} else {
				$unfeatured_items[] = $i;
			}
		}


		$this->element_data['items'] = new ArrayIterator($unfeatured_items);
		$this->element_data['features'] = new ArrayIterator($featured_items);

		/*
		if ($item['available_units'] != 0) {
			$this->element_data['is_available'] = true;
		} else {
			$this->element_data['is_available'] = false;
		}
		*/

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
			/*
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
			*/
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
		} elseif (isset($_REQUEST['state'])) {
			if ($_REQUEST['state'] == 'cart') {
				$this->setTemplate('cart');
			}
		}
		return $this->element_data;
	}
} // END class
?>
