<?php
/**
 * CommercePlant manages products/offers/orders, records transactions, and
 * deals with payment processors
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class CommercePlant extends PlantBase {
	
	public function __construct($request_type,$request) {
		$this->request_type = 'commerce';
		$this->plantPrep($request_type,$request);
	}
	
	public function processRequest() {
		if ($this->action) {
			$this->routing_table = array(
				// alphabetical for ease of reading
				// first value  = target method to call
				// second value = allowed request methods (string or array of strings)
				'additem'          => array('addItem','direct'),
				'addorder'         => array('addOrder','direct'),
				'addtransaction'   => array('addTransaction','direct'),
				'deleteitem'       => array('deleteItem','direct'),
				'edititem'         => array('editItem','direct'),
				'editorder'        => array('editOrder','direct'),
				'edittransaction'  => array('editTransaction','direct'),
				'getitem'          => array('getItem','direct'),
				'getitemsforuser'  => array('getItemsForUser','direct'),
				'getorder'         => array('getOrder','direct'),
				'gettransaction'   => array('getTransaction','direct'),
				'finalizepayment'  => array('finalizeRedirectedPayment',array('get','post','direct')),
				'initiatecheckout' => array('initiateCheckout',array('get','post','direct'))
			);
			// see if the action matches the routing table:
			$basic_routing = $this->routeBasicRequest();
			if ($basic_routing !== false) {
				return $basic_routing;
			} else {
				return false;
			}
		} else {
			return $this->response->pushResponse(
				400,
				$this->request_type,
				$this->action,
				false,
				'no action specified'
			);
		}
	}
	
	protected function addItem(
		$user_id,
		$name,
		$description='',
		$sku='',
		$price=0,
		$available_units=-1,
		$digital_fulfillment=0,
		$physical_fulfillment=0,
		$physical_weight=0,
		$physical_width=0,
		$physical_height=0,
		$physical_depth=0,
		$variable_pricing=0,
		$fulfillment_asset=0,
		$descriptive_asset=0
	   ) {
		$result = $this->db->setData(
			'items',
			array(
				'user_id' => $user_id,
				'name' => $name,
				'description' => $description,
				'sku' => $sku,
				'price' => $price,
				'available_units' => $available_units,
				'digital_fulfillment' => $digital_fulfillment,
				'physical_fulfillment' => $physical_fulfillment,
				'physical_weight' => $physical_weight,
				'physical_width' => $physical_width,
				'physical_height' => $physical_height,
				'physical_depth' => $physical_depth,
				'variable_pricing' => $variable_pricing,
				'fulfillment_asset' => $fulfillment_asset,
				'descriptive_asset' => $descriptive_asset
			)
		);
		return $result;
	}
	
	protected function getItem($id) {
		$result = $this->db->getData(
			'items',
			'*',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $id
				)
			)
		);
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}
	
	protected function editItem(
		$id,
		$name=false,
		$description=false,
		$sku=false,
		$price=false,
		$available_units=false,
		$digital_fulfillment=false,
		$physical_fulfillment=false,
		$physical_weight=false,
		$physical_width=false,
		$physical_height=false,
		$physical_depth=false,
		$variable_pricing=false,
		$fulfillment_asset=false,
		$descriptive_asset=false
	   ) {
		$final_edits = array_filter(
			array(
				'name' => $name,
				'description' => $description,
				'sku' => $sku,
				'price' => $price,
				'available_units' => $available_units,
				'digital_fulfillment' => $digital_fulfillment,
				'physical_fulfillment' => $physical_fulfillment,
				'physical_weight' => $physical_weight,
				'physical_width' => $physical_width,
				'physical_height' => $physical_height,
				'physical_depth' => $physical_depth,
				'variable_pricing' => $variable_pricing,
				'fulfillment_asset' => $fulfillment_asset,
				'descriptive_asset' => $descriptive_asset
			),
			'CASHSystem::notExplicitFalse'
		);
		$result = $this->db->setData(
			'items',
			$final_edits,
			array(
				'id' => array(
					'condition' => '=',
					'value' => $id
				)
			)
		);
		return $result;
	}
	
	protected function deleteItem($id) {
		$result = $this->db->deleteData(
			'items',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $id
				)
			)
		);
		return $result;
	}

	protected function getItemsForUser($user_id) {
		$result = $this->db->getData(
			'items',
			'*',
			array(
				"user_id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			)
		);
		return $result;
	}

	protected function addOrder(
		$user_id,
		$order_contents,
		$customer_user_id=0,
		$transaction_id=-1,
		$fulfilled=0,
		$notes='',
		$country_code=''
	) {
		if (is_array($order_contents)) {
			/*
			TO-DO: ensure that we're storing an array of:
				items as at least:
					id
					name
					description
					price
					physical (bool)
					digital (bool)
				and maybe just all traits...yeah?
				
				basically we store as JSON to prevent loss of order history
				in the event an item changes or is deleted. we want accurate 
				history so folks don't get all crazy bananas about teh $$s
			*/
			$final_order_contents = json_encode($order_contents);
			$result = $this->db->setData(
				'orders',
				array(
					'user_id' => $user_id,
					'customer_user_id' => $customer_user_id,
					'transaction_id' => $transaction_id,
					'order_contents' => $final_order_contents,
					'fulfilled' => $fulfilled,
					'notes' => $notes,
					'country_code' => $country_code
				)
			);
			return $result;
		} else {
			return false;
		}
	}
	
	protected function getOrder($id) {
		$result = $this->db->getData(
			'orders',
			'*',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $id
				)
			)
		);
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}
	
	protected function editOrder(
		$id,
		$order_contents=false,
		$transaction_id=false,
		$fulfilled=false,
		$notes=false,
		$country_code=false
	) {
		if ($order_contents) {
			$order_contents = json_encode($order_contents);
		}
		$final_edits = array_filter(
			array(
				'transaction_id' => $transaction_id,
				'order_contents' => $order_contents,
				'fulfilled' => $fulfilled,
				'notes' => $notes,
				'country_code' => $country_code
			),
			'CASHSystem::notExplicitFalse'
		);
		$result = $this->db->setData(
			'orders',
			$final_edits,
			array(
				'id' => array(
					'condition' => '=',
					'value' => $id
				)
			)
		);
		return $result;
	}
	
	protected function addTransaction(
		$user_id,
		$connection_id,
		$connection_type,
		$service_timestamp='',
		$service_transaction_id='',
		$data_sent='',
		$data_returned='',
		$successful=-1,
		$gross_price=0,
		$service_fee=0
	) {
		$result = $this->db->setData(
			'transactions',
			array(
				'user_id' => $user_id,
				'connection_id' => $connection_id,
				'connection_type' => $connection_type,
				'service_timestamp' => $service_timestamp,
				'service_transaction_id' => $service_transaction_id,
				'data_sent' => $data_sent,
				'data_returned' => $data_returned,
				'successful' => $successful,
				'gross_price' => $gross_price,
				'service_fee' => $service_fee
			)
		);
		return $result;
	}
	
	protected function getTransaction($id) {
		$result = $this->db->getData(
			'transactions',
			'*',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $id
				)
			)
		);
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}
	
	protected function editTransaction(
		$id,
		$data_sent=false,
		$data_returned=false,
		$successful=false,
		$gross_price=false,
		$service_fee=false
	) {
		$final_edits = array_filter(
			array(
				'data_sent' => $data_sent,
				'data_returned' => $data_returned,
				'successful' => $successful,
				'gross_price' => $gross_price,
				'service_fee' => $service_fee
			),
			'CASHSystem::notExplicitFalse'
		);
		$result = $this->db->setData(
			'transactions',
			$final_edits,
			array(
				'id' => array(
					'condition' => '=',
					'value' => $id
				)
			)
		);
		return $result;
	}
	
	protected function initiateCheckout($user_id,$connection_id,$order_contents=false,$item_id=false) {
		if (!$order_contents && !$item_id) {
			return false;
		} else {
			if (!$order_contents) {
				$order_contents = array();
			}
			if ($item_id) {
				$order_contents[] = $this->getItem($item_id);
			}
			$transaction_id = $this->addTransaction(
				$user_id,
				$connection_id,
				$this->getConnectionType($connection_type)
			);
			$order_id = $this->addOrder(
				$user_id,
				$order_contents,
				0,
				$transaction_id
			);
			if ($order_id) {
				$success = $this->initiatePaymentRedirect($order_id);
				return $success;
			} else {
				return false;
			}
		}
	}
	
	protected function getOrderTotals($order_contents) {
		$contents = json_decode($order_contents,true);
		$return_array = array(
			'price' => 0,
			'description' => ''
		);
		foreach($contents as $item) {
			$return_array['price'] += $item['price'];
			$return_array['description'] .= $item['name'] . "\n";
		}
		return $return_array;
	}
	
	protected function initiatePaymentRedirect($order_id) {
		$order_details = $this->getOrder($order_id);
		$order_totals = $this->getOrderTotals($order_details['order_contents']);
		$connection_type = $this->getConnectionType($order_details['connection_id']);
		switch ($connection_type) {
			case 'com.paypal':
				$pp = new PaypalSeed($order_details['user_id'],$order_details['connection_id']);
				$redirect_url = $pp->setExpressCheckout(
					$order_totals['price'],
					'order-' . $order_id,
					$order_totals['description'],
					CASHSystem::getCurrentURL() . '?cash_request_type=commerce&cash_action=finalizepayment&order_id=' . $order_id . '&creation_date=' . $order_details['creation_date'],
					CASHSystem::getCurrentURL()
				);
				$redirect = CASHSystem::redirectToUrl($redirect_url);
				// the return will only happen if headers have already been sent
				// if they haven't redirectToUrl() will handle it and call exit
				return $redirect;
				break;
		    default:
				return false;
		}
		return $final_redirect;
	}
	
	protected function finalizeRedirectedPayment($order_id,$creation_date,$full_cash_request) {
		// TODO: finalize checkout, alter order/transaction, return order_id on success
	}
	
} // END class 
?>