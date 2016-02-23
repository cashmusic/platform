<?php
/**
 * Single Purchase element
 *
 * @package singlepurchase.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2016, CASH Music
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


		$this->element_data['paypal_connection'] = false;
		$this->element_data['stripe_public_key'] = false;
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'getsettings',
				'type' => 'payment_defaults',
				'user_id' => $this->element_data['user_id']
			)
		);
		if (is_array($settings_request->response['payload'])) {
			if ($settings_request->response['payload']['pp_default'] || $settings_request->response['payload']['pp_micro']) {
				$this->element_data['paypal_connection'] = true;
			}
			if ($settings_request->response['payload']['stripe_default']) {
				$payment_seed = new StripeSeed($this->element_data['user_id'],$settings_request->response['payload']['stripe_default']);
				if (!empty($payment_seed->publishable_key)) {
					$this->element_data['stripe_public_key'] = $payment_seed->publishable_key;
				}
			}
		} else {
			if (isset($this->element_data['connection_id'])) {
				$connection_settings = CASHSystem::getConnectionTypeSettings($this->element_data['connection_type']);
		      $seed_class = $connection_settings['seed'];
				if ($seed_class == 'StripeSeed') {
					$payment_seed = new StripeSeed($this->element_data['user_id'],$this->element_data['connection_id']);
					if (!empty($payment_seed->publishable_key)) {
						$this->element_data['stripe_public_key'] = $payment_seed->publishable_key;
					}
				} elseif ($seed_class == 'PaypalSeed') {
					$this->element_data['paypal_connection'] = true;
				}
			}
		}


      if (!$this->element_data['paypal_connection'] && !$this->element_data['stripe_public_key']) {
         $this->setError("No valid payment connection found.");
      }

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
		} elseif (isset($_REQUEST['initiate_checkout'])) {

			// we've submitted the default stage, so we need to check if we're going to shipping stage next, or skipping to init payment stage
			$total_price = $item['price'];
			if (isset($_REQUEST['total_price'])) {
				$total_price = $_REQUEST['total_price'];
			}
			$this->element_data['total_price'] = $total_price;

			if ($item['physical_fulfillment'] == 1) {

				// according to the item, we've got a product with physical fulfillment--- let's assume it's shippable
				// and revert to no shipping if it doesn't meet the basic requirements on shipping regions
				$this->element_data['no_shipping'] = false;

				/** LEGACY NOTICE: shipping has moved from elements to items. */
				if ($item['shipping']) {
					// check if item has shipping first (new)
					if ($item['shipping']['r1-1'] + $item['shipping']['r2-1'] == 0.00) {
						$this->element_data['no_shipping'] = true;
					} else {
						// we've got shipping set via the new item standard, so let's give them precedence over the legacy values
						$this->element_data['region1_cost'] = $item['shipping']['r1-1'];
						$this->element_data['region2_cost'] = $item['shipping']['r2-1'];
					}
				}
				else {
					// else check if element has shipping (legacy)
					if ($this->element_data['region1_cost'] + $this->element_data['region2_cost'] == 0.00) {
						$this->element_data['no_shipping'] = true;
					}
				}
			} else {
				$this->element_data['no_shipping'] = true;
			}

			if ($total_price < $item['price']) {
				// okay, someone's a wiseguy and trying to change the price on the checkout form
				$this->element_data['error_message'] = 'Make sure you enter a price of at least ' . $this->element_data['currency'] . $item['price'] . ' and try again.';
			}
			else {
				$this->setTemplate('checkout');
			}
		}

		elseif (isset($_REQUEST['get_shipping'])) {
			// we need to save values for access in init_payment
			$request = new CASHRequest();
			$request->sessionSet("order_data", json_encode(
				array(
					"shipping" => $_REQUEST['shipping'],
					"first_name" => $_REQUEST['first_name'],
					"last_name" => $_REQUEST['last_name'],
					"street_address" => $_REQUEST['street_address'],
					"street_address2" => $_REQUEST['street_address2'],
					"city" => $_REQUEST['city'],
					"province" => $_REQUEST['province'],
					"postal_code" => $_REQUEST['postal_code'],
					"country"	  => $_REQUEST['country']
				)
			));

			$this->element_data['total_price'] = $_REQUEST['total_price'];
			$this->setTemplate('init_payment');
		}
		return $this->element_data;
	}
} // END class
?>
