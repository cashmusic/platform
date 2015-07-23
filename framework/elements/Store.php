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
		$this->element_data['public_url'] = CASH_PUBLIC_URL;
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'getitemsforuser',
				'user_id' => $this->element_data['user_id']
			)
		);
		$items = $item_request->response['payload'];
		foreach ($items as &$item) {
			$item['price'] = number_format($item['price'], 2, '.', '');
			if ($item['available_units'] != 0) {
				$item['is_available'] = true;
			} else {
				$item['is_available'] = false;
			}
			if ($item['variants']) {
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
								$this_frag = $attribute['key'] . '->' . $i['key']; // current attribute id for qty
								$counter_options = array();
								$defaultArray = array();
								foreach ($counter_attribute['items'] as $ci) {
									if (!in_array($ci['key'],$defaultArray)) {
										// here we're storing the default "other" dropdown for JS to use
										if ($ci['value'] > 0) { // check qty here too
											$defaultArray[] = $ci['key'];
										}
									}
									$that_frag = $counter_key.'->'.$ci['key']; // other attribute id
									// combine attribute ids to get the quantity key
									if ($counter_index == 1) {
										$qty_key = $this_frag.'+'.$that_frag;
									} else {
										$qty_key = $that_frag.'+'.$this_frag;
									}
									// we need to loop through our list of quantities and check keys
									foreach ($item['variants']['quantities'] as $q) {
										if ($q['key'] == $qty_key && $q['value']) { // check that value > 0
											$counter_options[] = $ci['key'];
											break;
										}
									}
								}
								$attribute['defaultcountermenu'] = str_replace("'","\'",json_encode($defaultArray));
								$i['countermenu'] = str_replace("'","\'",json_encode($counter_options));
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
		}


		$cart_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'getcart'
			)
		);
		$cart = $cart_request->response['payload'];
		if ($cart) {
			$this->element_data['items_in_cart'] = true;
		}

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
		} elseif ($this->status_uid == 'commerce_finalizepayment_400' || $this->status_uid == 'element_redeemcode_400') {
			// payerid is specific to paypal, so this is temporary to tell between canceled and errored:
			if (isset($_GET['PayerID'])) {
				//$this->element_data['error_message'] = $this->options['message_error'];
				$this->element_data['error_message'] = print_r($this->original_response,true);
			}
		} elseif (isset($_REQUEST['state'])) {
			if ($_REQUEST['state'] == 'cart') {
				$subtotal = 0;
				$shipping = 0;
				foreach ($cart as &$i) {
					foreach ($items as $ii) {
						if ($ii['id'] == $i['id']) {
							$i['price'] = max($i['price'],$ii['price']);
							$i['total_price'] = number_format($i['qty'] * $i['price'],2);
							//$i['shipping_r1'] = $ii['shipping']['r1-1'];
							if ($ii['shipping']) {
								if (isset($ii['shipping']['r1-1'])) {
									$i['shipping_r1'] = $ii['shipping']['r1-1'];
									$i['shipping_r1rest'] = $ii['shipping']['r1-1+'];
									$i['shipping_r2'] = $ii['shipping']['r2-1'];
									$i['shipping_r2rest'] = $ii['shipping']['r2-1+'];
									$shipping += $i['shipping_r1rest']*($i['qty']-1)+$i['shipping_r1'];
								}
							}
							$subtotal += $i['total_price'];
							$i['name'] = $ii['name'];
							if ($i['variant']) {
								$i['variant_fixed'] = str_replace(' ','+',$i['variant']);
								foreach ($ii['variants']['quantities'] as $q) {
									if ($q['key'] == str_replace(' ','+',$i['variant'])) { //TODO: hacky fix for plus signs decoded as spaces
										$i['variant_name'] = $q['formatted_name'];
										break;
									}
								}
							}
							break;
						}
					}
				}
				//$this->element_data['cart_dump'] = print_r($cart,true);
				$this->element_data['cart'] = new ArrayIterator($cart);;
				$this->element_data['subtotal'] =  number_format($subtotal,2);
				$this->element_data['shipping'] =  number_format($shipping,2);
				$this->element_data['total'] =  number_format($subtotal+$shipping,2);;
				$this->setTemplate('cart');
			}
		}
		return $this->element_data;
	}
} // END class
?>
