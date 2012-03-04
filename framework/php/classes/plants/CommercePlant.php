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
				'getorder'         => array('getOrder','direct'),
				'gettransaction'   => array('getTransaction','direct')
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
		$physical_depth=0
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
				'physical_depth' => $physical_depth
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
		$physical_depth=false
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
				'physical_depth' => $physical_depth
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
	
	protected function addAssetToItem($item_id,$asset_id,$type='download') {
		$result = $this->db->setData(
			'commerce_assets',
			array(
				'scope_table_alias' => 'items',
				'scope_table_id' => $item_id,
				'asset_id' => $asset_id,
				'type' => $type
			)
		);
		return $result;
	}
	
	protected function getAssetsForItem($item_id) {
		$result = $this->db->getData(
			'commerce_assets',
			'*',
			array(
				'scope_table_alias' => array(
					'condition' => '=',
					'value' => 'items'
				),
				'scope_table_id' => array(
					'condition' => '=',
					'value' => $item_id
				)
			)
		);
		// TODO:
		// loop through each returned asset and get al its details, return as
		// on big array rather than an array of IDs
		return $result;
	}
	
	protected function deleteAssetsForItem($item_id) {
		$result = $this->db->deleteData(
			'commerce_assets',
			array(
				'scope_table_alias' => array(
					'condition' => '=',
					'value' => 'items'
				),
				'scope_table_id' => array(
					'condition' => '=',
					'value' => $item_id
				)
			)
		);
		return $result;
	}
	
	protected function addOrder(
		$user_id,
		$order_contents,
		$customer_user_id,
		$transaction_id=-1,
		$fulfilled=0,
		$notes=''
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
					'notes' => $notes
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
		$notes=false
	) {
		if ($order_contents) {
			$order_contents = json_encode($order_contents);
		}
		$final_edits = array_filter(
			array(
				'transaction_id' => $transaction_id,
				'order_contents' => $order_contents,
				'fulfilled' => $fulfilled,
				'notes' => $notes
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
	
	protected function initiatePaymentRedirect() {}
	
	protected function finalizeRedirectedPayment() {}
	
} // END class 
?>