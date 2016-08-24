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
		$this->element_data['public_url'] = CASH_PUBLIC_URL;
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
		if ($item['descriptive_asset']) {
			$item_image_request = new CASHRequest(
				array(
					'cash_request_type' => 'asset',
					'cash_action' => 'getpublicurl',
					'id' => $item['descriptive_asset'],
					'user_id' => $this->element_data['user_id']
				)
			);
			$this->element_data['item_image_url'] = $item_image_request->response['payload'];
		}

		// shipping
		if ($item['physical_fulfillment'] == 1) {
			// according to the item, we've got a product with physical fulfillment--- let's assume it's shippable
			// and revert to no shipping if it doesn't meet the basic requirements on shipping regions
			$this->element_data['no_shipping'] = false;

			$settings_request = new CASHRequest(
				array(
					'cash_request_type' => 'system',
					'cash_action' => 'getsettings',
					'type' => 'regions',
					'user_id' => $this->element_data['user_id']
				)
			);
			if ($settings_request->response['payload']) {
				$this->element_data['region1_name'] = $settings_request->response['payload']['region1'];
				$this->element_data['region2_name'] = $settings_request->response['payload']['region2'];
			}

			/** LEGACY NOTICE: shipping has moved from elements to items. */
			if ($item['shipping']) {
				// we've got shipping set via the new item standard, so let's give them precedence over the legacy values
				if (isset($item['shipping']['r1-1'])) {
					$this->element_data['region1_cost'] = number_format($item['shipping']['r1-1'], 2);
					$this->element_data['region2_cost'] = number_format($item['shipping']['r2-1'],2);
				}
			}
			// fallback for error shipping
			if (!isset($this->element_data['region1_cost']) && !isset($this->element_data['region2_cost'])) {
				$this->element_data['region1_cost'] = '0.00';
				$this->element_data['region2_cost'] = '0.00';
			}
			// fallback for empty regions
			if (!isset($this->element_data['region1_name'])) {
				$this->element_data['region1_name'] = 'US';
				$this->element_data['region2_name'] = 'International';
			}
		} else {
			$this->element_data['no_shipping'] = true;
		}

		// item variants...ugh
		if ($item['variants']) {
			$this->element_data['has_variants'] = true;
			$this->element_data['json_keys'] = (bool) json_decode($item['variants']['quantities'][0]['key']);
			$this->element_data['attributes_count'] = count($item['variants']['attributes']);
			$verified_attributes = array();
			foreach ($item['variants']['attributes'] as $key => $attribute) {
				$attribute['index'] = $key;
				$attribute['name'] = ''.strtolower(str_replace(' ','',$attribute['key']));
				$verified_items = array();
				foreach ($attribute['items'] as $i) {
					if ($i['value'] > 0) { // this means we've got some quantity for this specific attribute
						if ($this->element_data['attributes_count'] > 1) { // check if we have multiple attribute types
							// hard coding for 2 attributes RN, sort out which is "other"
							$counter_index = 1;
							if ($attribute['index'] == 1) {
								$counter_index = 0;
							}
							$counter_attribute = $item['variants']['attributes'][$counter_index];
							$counter_key = $counter_attribute['key'];
							if ($this->element_data['json_keys']) {
								$this_frag = array($attribute['key'] => $i['key']);
							} else {
								$this_frag = $attribute['key'] . '->' . $i['key']; // current attribute id for qty
							}

							$counter_options = array();
							$defaultArray = array();
							foreach ($counter_attribute['items'] as $ci) {
								if (!in_array($ci['key'],$defaultArray)) {
									// here we're storing the default "other" dropdown for JS to use
									if ($ci['value'] > 0) { // check qty here too
										$defaultArray[] = $ci['key'];
									}
								}
								if ($this->element_data['json_keys']) {
									$that_frag = array($counter_key => $ci['key']);
									// combine attribute ids to get the quantity key
									if ($counter_index == 1) {
										$qty_key = json_encode(array_merge($this_frag,$that_frag));
									} else {
										$qty_key = json_encode(array_merge($that_frag,$this_frag));
									}
								} else {
									$that_frag = $counter_key.'->'.$ci['key']; // other attribute id
									// combine attribute ids to get the quantity key
									if ($counter_index == 1) {
										$qty_key = $this_frag.'+'.$that_frag;
									} else {
										$qty_key = $that_frag.'+'.$this_frag;
									}
								}

								// we need to loop through our list of quantities and check keys
								foreach ($item['variants']['quantities'] as $q) {
									if ($q['key'] == $qty_key && $q['value']) { // check that value > 0
										$counter_options[] = $ci['key'];
										break;
									}
								}
							}
							$i['keyvalue'] = $this_frag; // set the
							$i['countermenu'] = str_replace("'","&apos;",json_encode($counter_options));
							$attribute['defaultcountermenu'] = str_replace("'","&apos;",json_encode($defaultArray));
						}
						$verified_items[] = $i;
					}
				}
				if (count($verified_items)) {
					$attribute['items'] = $verified_items;
					$verified_attributes[] = $attribute;
				}
			}
			$this->element_data['attributes'] = $verified_attributes;
		}

		// payment connection settings
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
			if (isset($settings_request->response['payload']['stripe_default'])) {
				if ($settings_request->response['payload']['stripe_default']) {
					$payment_seed = new StripeSeed($this->element_data['user_id'],$settings_request->response['payload']['stripe_default']);
					if (!empty($payment_seed->publishable_key)) {
						$this->element_data['stripe_public_key'] = $payment_seed->publishable_key;
					}
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
			($this->status_uid == 'commerce_finalizepayment_200') ||
			$this->status_uid == 'element_redeemcode_200' ||
			$this->status_uid == 'commerce_initiatecheckout_200' && $this->original_response['payload'] == 'force_success'
			) {
			if ($item['fulfillment_asset'] != 0) {
				$fulfillment_request = new CASHRequest(
					array(
						'cash_request_type' => 'asset',
						'cash_action' => 'getfulfillmentassets',
						'asset_details' => $item['fulfillment_asset'],
						'session_id' => $this->session_id
					)
				);
				if ($fulfillment_request->response['payload']) {
					$this->element_data['fulfillment_assets'] = new ArrayIterator($fulfillment_request->response['payload']);
				}
			}
			$this->unlock();
			$this->element_data['showsuccess'] = true;
		} elseif ($this->status_uid == 'commerce_initiatecheckout_400') {
			// could happen on a database glitch, but probably means the user set a pay-minimum price below the
			// minimum price. what a heel.
			$this->element_data['error_message'] = 'Make sure you enter a price of at least ' . $this->element_data['currency'] . $item['price'] . ' and try again.';
		} elseif ($this->status_uid == 'commerce_finalizepayment_400' || $this->status_uid == 'element_redeemcode_400') {
			// payerid is specific to paypal, so this is temporary to tell between canceled and errored:
			if ($this->unlocked) {
				// if it's unlocked and we get a 400 it means we're in an embed that's put on a page that invoked
				// a CASHRequest and caught the purchase return first. AKA: a band.cashmusic.org page
				$this->element_data['showsuccess'] = true;
			} else {
				if (isset($_GET['PayerID'])) {
					//$this->element_data['error_message'] = $this->options['message_error'];
					$this->element_data['error_message'] = print_r($this->original_response,true);
				}
			}
		}

		if (isset($_REQUEST['state'])) {
			if ($_REQUEST['state'] == 'success') {
				if ($this->unlocked) {
					if ($item['fulfillment_asset'] != 0) {
						$fulfillment_request = new CASHRequest(
							array(
								'cash_request_type' => 'asset',
								'cash_action' => 'getfulfillmentassets',
								'asset_details' => $item['fulfillment_asset'],
								'session_id' => $this->session_id
							)
						);
						if ($fulfillment_request->response['payload']) {
							$this->element_data['fulfillment_assets'] = new ArrayIterator($fulfillment_request->response['payload']);
						}
					}

					$this->setTemplate('success');
					$this->lock();
				}
			}
		}

		return $this->element_data;
	}
} // END class
?>
