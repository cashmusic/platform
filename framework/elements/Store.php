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
		$this->element_data['element_id'] = $this->element_id;
		$this->element_data['public_url'] = CASH_PUBLIC_URL;
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'getitemsforuser',
				'user_id' => $this->element_data['user_id']
			)
		);
		$items = $item_request->response['payload'];
		$indexed_items = array();
		foreach ($items as &$item) {
			$item['price'] = number_format($item['price'], 2, '.', '');
			if ($item['available_units'] != 0) {
				$item['is_available'] = true;
			} else {
				$item['is_available'] = false;
			}
			if ($item['descriptive_asset']) {
				$item_image_request = new CASHRequest(
					array(
						'cash_request_type' => 'asset',
						'cash_action' => 'getpublicurl',
						'id' => $item['descriptive_asset'],
						'user_id' => $this->element_data['user_id']
					)
				);
				$item['image_url'] = $item_image_request->response['payload'];
			}
			if ($item['variants']) {
				$item['json_keys'] = (bool) json_decode($item['variants']['quantities'][0]['key']);
				$item['has_variants'] = true;
				$verified_attributes = array();
				$item['attributes_count'] = count($item['variants']['attributes']);
				foreach ($item['variants']['attributes'] as $key => $attribute) {
					$attribute['index'] = $key;
					$attribute['name'] = ''.strtolower(str_replace(' ','',$attribute['key']));
					$verified_items = array();
					foreach ($attribute['items'] as $i) {
						if ($i['value'] > 0) { // this means we've got some quantity for this specific attribute
							if ($item['attributes_count'] > 1) { // check if we have multiple attribute types
								// hard coding for 2 attributes RN, sort out which is "other"
								$counter_index = 1;
								if ($attribute['index'] == 1) {
									$counter_index = 0;
								}
								$counter_attribute = $item['variants']['attributes'][$counter_index];
								$counter_key = $counter_attribute['key'];
								if ($item['json_keys']) {
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
									if ($item['json_keys']) {
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
				$item['attributes'] = $verified_attributes;
			}
			$indexed_items[$item['id']] = $item;
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
		}

		$cart_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'getcart',
				'session_id' => $this->session_id
			)
		);
		$cart = $cart_request->response['payload'];
		if ($cart) {
			if (is_array($cart)) {
				$checkcount = count($cart);
				if (isset($cart['shipto'])) {
					$checkcount = $checkcount-1;
				}
				if ($checkcount) {
					$this->element_data['items_in_cart'] = true;
				}
			}
		} else {
			$cart = array();
		}

		$featured_items = array();
		$unfeatured_items = array();
		if (is_array($this->element_data['featured_items'])) {
			foreach ($this->element_data['featured_items'] as $i) {
				$featured_items[] = $indexed_items[$i['item_id']];
			}
		}
		if (is_array($this->element_data['additional_items'])) {
			foreach ($this->element_data['additional_items'] as $i) {
				$unfeatured_items[] = $indexed_items[$i['item_id']];
			}
		}


		$this->element_data['items'] = new ArrayIterator($unfeatured_items);
		$this->element_data['features'] = new ArrayIterator($featured_items);

		// get currency info for element owner
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

		// get region information for element owner
		// now get the current setting
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'getsettings',
				'type' => 'regions',
				'user_id' => $this->element_data['user_id']
			)
		);
		if ($settings_request->response['payload']) {
			$this->element_data['region1'] = $settings_request->response['payload']['region1'];
			$this->element_data['region2'] = $settings_request->response['payload']['region2'];
		} else {
			$this->element_data['region1'] = 'US';
			$this->element_data['region2'] = 'International';
		}

		if (
			$this->status_uid == 'commerce_finalizepayment_200' ||
			$this->status_uid == 'element_redeemcode_200'
			) {
			if ($this->status_uid == 'commerce_finalizepayment_200') {
				$this->element_data['order_id'] = $this->original_response['payload'];
				$verified = true;
			} else if ($this->status_uid == 'element_redeemcode_200') {
				$this->element_data['order_id'] = $_GET['order_id'];
				$verified = false;
			}

			$order_request = new CASHRequest(
				array(
					'cash_request_type' => 'commerce',
					'cash_action' => 'getorder',
					'id' => $this->element_data['order_id'],
					'deep' => true
				)
			);
			$order_details = $order_request->response['payload'];

			if ($order_details) {
				if ($this->status_uid == 'element_redeemcode_200') {
					if ($_GET['email'] == $order_details['customer_details']['email_address']) {
						$verified = true;
					}
				}
				if ($verified) {
					$this->unlock();
					$this->element_data['showsuccess'] = true;
				}
			}
		} elseif ($this->status_uid == 'commerce_finalizepayment_400' || $this->status_uid == 'element_redeemcode_400') {
			if ($this->unlocked) {
				// If we're seeing a payment error AND the element is unlocked it means the payment was
				// processed in the top window and we're in an embed. This will show up as a success AND
				// re-lock the element on success template display.
				$this->element_data['showsuccess'] = true;
			} else {
				// payerid is specific to paypal, so this is temporary to tell between canceled and errored:
				if (isset($_GET['PayerID'])) {
					//$this->element_data['error_message'] = $this->options['message_error'];
					$this->element_data['error_message'] = print_r($this->original_response,true);
				}
			}
		} elseif (isset($_REQUEST['state'])) {
			if ($_REQUEST['state'] == 'cart') {
				$subtotal = 0;
				$shipping = 0;
				$shippingr1 = 0;
				$shippingr2 = 0;
				$physical = false;
				$shipto = false;
				if (isset($cart['shipto'])) {
					if ($cart['shipto'] == 'r2') {
						$this->element_data['shiptor2'] = true;
					} else if ($cart['shipto'] == 'r1') {
						$this->element_data['shiptor1'] = true;
					}
					$shipto = $cart['shipto'];
					unset($cart['shipto']);
				}
				foreach ($cart as $key => &$i) {
					foreach ($items as $ii) {
						if ($ii['id'] == $i['id']) {
							$i['price'] = max($i['price'],$ii['price']);
							$i['total_price'] = number_format($i['qty'] * $i['price'],2);
							//$i['shipping_r1'] = $ii['shipping']['r1-1'];
							if ($ii['physical_fulfillment']) {
								if (!$physical) {
									$physical = true;
								}
								if ($ii['shipping']) {
									if (isset($ii['shipping']['r1-1'])) {
										$i['shipping_r1'] = $ii['shipping']['r1-1'];
										$i['shipping_r1rest'] = $ii['shipping']['r1-1+'];
										$i['shipping_r2'] = $ii['shipping']['r2-1'];
										$i['shipping_r2rest'] = $ii['shipping']['r2-1+'];
										if ($shipto == 'r1' || $shipto == 'r2') {
											$shipping += $i['shipping_'.$shipto.'rest']*($i['qty']-1)+$i['shipping_'.$shipto];
										}
										$shippingr1 += $i['shipping_r1rest']*($i['qty']-1)+$i['shipping_r1'];
										$shippingr2 += $i['shipping_r2rest']*($i['qty']-1)+$i['shipping_r2'];
									}
								}
							}
							$subtotal += $i['total_price'];
							$i['name'] = $ii['name'];
							if ($i['variant']) {
								foreach ($ii['variants']['quantities'] as $q) {
									$decoded_key = json_decode($q['key'],true,1) ?: $q['key'];
									if ($decoded_key == $i['variant']) { //TODO: hacky fix for plus signs decoded as spaces
										$i['variant_id']     = $q['id'];
										$i['variant_key']    = $q['key'];
										$i['variant_js_key'] = str_replace("'","\'",$q['key']);
										$i['variant_name']   = $q['formatted_name'];
										break;
									}
								}
							}
							break;
						}
					}
				}
				$this->element_data['has_physical'] = $physical;
				$this->element_data['cart'] = new ArrayIterator($cart);
				$this->element_data['subtotal'] =  number_format($subtotal,2);
				if ($shipto) {
					$this->element_data['shipping'] =  number_format($shipping,2);
				} else {
					$this->element_data['shipping'] = 'TBD';
				}
				$this->element_data['shippingr1'] =  number_format($shippingr1,2);
				$this->element_data['shippingr2'] =  number_format($shippingr2,2);
				$this->element_data['total'] =  number_format($subtotal+$shipping,2);;
				$this->setTemplate('cart');
			}
			if ($_REQUEST['state'] == 'success') {
				if ($this->unlocked) {
					$this->lock();
					$order_request = new CASHRequest(
						array(
							'cash_request_type' => 'commerce',
							'cash_action' => 'getorder',
							'id' => $this->element_data['order_id'],
							'deep' => true
						)
					);
					$order_details = $order_request->response['payload'];

					if ($order_details) {
						$order_contents = json_decode($order_details['order_contents'],true);
						$this->element_data['has_physical'] = false;
						$this->element_data['item_subtotal'] = 0;
						foreach ($order_contents as &$i) {
							$i['total_price'] = number_format($i['qty'] * $i['price'],2);
							$this->element_data['item_subtotal'] += $i['total_price'];
							if ($i['fulfillment_asset'] != 0) {
								$fulfillment_request = new CASHRequest(
									array(
										'cash_request_type' => 'asset',
										'cash_action' => 'getfulfillmentassets',
										'asset_details' => $i['fulfillment_asset']
									)
								);
								if ($fulfillment_request->response['payload']) {
									$i['digital_fulfillment_details'] = new ArrayIterator($fulfillment_request->response['payload']);
								}
							}
							if (!$this->element_data['has_physical'] && $i['physical_fulfillment']) {
								$this->element_data['has_physical'] = true;
							}
							if ($i['variant']) {
								$variant_request = new CASHRequest(
									array(
										'cash_request_type' => 'commerce',
										'cash_action' => 'getitemvariants',
										'item_id' => $i['id']
									)
								);
								if ($variant_request->response['payload']) {
									if (is_array($variant_request->response['payload'])) {
										foreach ($variant_request->response['payload']['quantities'] as $v) {
											if ($v['key'] == $i['variant']) {
												$i['variant_name'] = $v['formatted_name'];
												break;
											}
										}
									}
								}
							}
						}
						$this->element_data['shipping_subtotal'] =  number_format($order_details['gross_price'] - $this->element_data['item_subtotal'],2);
						$this->element_data['item_subtotal'] = number_format($this->element_data['item_subtotal'],2);
						$this->element_data['total'] = number_format($order_details['gross_price'],2);
						$this->element_data['order_contents'] = $order_contents;
						$this->setTemplate('success');
					}
				}
			}
		}
		return $this->element_data;
	}
} // END class
?>
