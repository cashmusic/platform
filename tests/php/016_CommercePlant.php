<?php
require_once('tests/php/base.php');
require_once('framework/php/classes/plants/CommercePlant.php');

class CommercePlantTests extends UnitTestCase {	
	var $testing_item,$testing_order,$testing_transaction;
	
	function testCommercePlant(){
		echo "Testing CommercePlant\n";
		
		$p = new CommercePlant('commerce', array());
		$this->assertIsa($p, 'CommercePlant');
	}
	
	function testAddItem() {
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'additem',
				'user_id' => 1,
				'name' => 'test item',
				'description' => 'this is a description for the test item',
				'sku' => 'sku #abc123',
				'price' => 5.97,
				'available_units' => 43,
				'digital_fulfillment' => 1,
				'physical_fulfillment' => 0,
				'physical_weight' => 1,
				'physical_width' => 2,
				'physical_height' => 3,
				'physical_depth' => 4
			)
		);
		// should work fine with no description or connection_id
		$this->assertTrue($item_request->response['payload']);
		$this->testing_item = $item_request->response['payload'];
	}
	
	function testEditAndGetItem() {
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'getitem',
				'id' => $this->testing_item
			)
		);
		// should work fine with no description or connection_id
		$this->assertTrue($item_request->response['payload']);
		$this->assertEqual($item_request->response['payload']['user_id'],1);
		$this->assertEqual($item_request->response['payload']['name'],'test item');
		$this->assertEqual($item_request->response['payload']['description'],'this is a description for the test item');
		$this->assertEqual($item_request->response['payload']['sku'],'sku #abc123');
		$this->assertEqual($item_request->response['payload']['price'],5.97);
		$this->assertEqual($item_request->response['payload']['available_units'],43);
		$this->assertEqual($item_request->response['payload']['digital_fulfillment'],1);
		$this->assertEqual($item_request->response['payload']['physical_fulfillment'],0);
		$this->assertEqual($item_request->response['payload']['physical_weight'],1);
		$this->assertEqual($item_request->response['payload']['physical_width'],2);
		$this->assertEqual($item_request->response['payload']['physical_height'],3);
		$this->assertEqual($item_request->response['payload']['physical_depth'],4);
		
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'edititem',
				'id' => $this->testing_item,
				'name' => 'this is a different name'
			)
		);
		$this->assertTrue($item_request->response['payload']);
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'getitem',
				'id' => $this->testing_item
			)
		);
		$this->assertEqual($item_request->response['payload']['name'],'this is a different name');
	}
	
	function testDeleteItem() {
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'deleteitem',
				'id' => $this->testing_item
			)
		);
		$this->assertTrue($item_request->response['payload']);
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'getitem',
				'id' => $this->testing_item
			)
		);
		$this->assertFalse($item_request->response['payload']);
	}
	
	function testAddOrder() {
		$order_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'addorder',
				'user_id' => 1,
				'customer_user_id' => 1000,
				'order_contents' => 'this will be a big chunk of JSON' // needs to be type array
			)
		);
		// will fail with order contents not an array
		$this->assertFalse($order_request->response['payload']);
		
		$contents_array = array('test','array');
		$order_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'addorder',
				'user_id' => 1,
				'customer_user_id' => 1000,
				'transaction_id' => -1,
				'order_contents' => $contents_array,
				'fulfilled' => 0,
				'notes' => 'and an optional note'
			)
		);
		$this->assertTrue($order_request->response['payload']);
		$this->testing_order = $order_request->response['payload'];
	}
	
	function testGetAndEditOrder() {
		if ($this->testing_order) {
			$order_request = new CASHRequest(
				array(
					'cash_request_type' => 'commerce', 
					'cash_action' => 'getorder',
					'id' => $this->testing_order
				)
			);
			// should work fine with no description or connection_id
			$this->assertTrue($order_request->response['payload']);
			$this->assertEqual($order_request->response['payload']['user_id'],1);
			$this->assertEqual($order_request->response['payload']['customer_user_id'],1000);
			$this->assertEqual($order_request->response['payload']['transaction_id'],-1);
			$this->assertEqual($order_request->response['payload']['order_contents'],json_encode(array('test','array')));
			$this->assertEqual($order_request->response['payload']['fulfilled'],0);
			$this->assertEqual($order_request->response['payload']['notes'],'and an optional note');
			
			$order_request = new CASHRequest(
				array(
					'cash_request_type' => 'commerce', 
					'cash_action' => 'editorder',
					'id' => $this->testing_order,
					'fulfilled' => 1,
					'transaction_id' => 764
				)
			);
			$this->assertTrue($order_request->response['payload']);
			$order_request = new CASHRequest(
				array(
					'cash_request_type' => 'commerce', 
					'cash_action' => 'getorder',
					'id' => $this->testing_order
				)
			);
			$this->assertEqual($order_request->response['payload']['fulfilled'],1);
			$this->assertEqual($order_request->response['payload']['transaction_id'],764);
		}
	}
	
	function testAddTransaction() {
		$transaction_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'addtransaction',
				'user_id' => 1,
				'connection_id' => 1,
				'connection_type' => 'com.paypal',
				'service_timestamp' => 'string not int — different formats',
				'service_transaction_id' => '123abc',
				'data_sent' => 'big JSON',
				'data_returned' => 'also big JSON',
				'successful' => -1,
				'gross_price' => 123.45,
				'service_fee' => 12.34
			)
		);
		// should work fine with no description or connection_id
		$this->assertTrue($transaction_request->response['payload']);
		$this->testing_transaction = $transaction_request->response['payload'];
	}
	
	function testGetAndEditTransaction() {
		$transaction_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'gettransaction',
				'id' => $this->testing_transaction
			)
		);
		$this->assertTrue($transaction_request->response['payload']);
		$this->assertEqual($transaction_request->response['payload']['user_id'],1);
		$this->assertEqual($transaction_request->response['payload']['connection_id'],1);
		$this->assertEqual($transaction_request->response['payload']['connection_type'],'com.paypal');
		$this->assertEqual($transaction_request->response['payload']['service_timestamp'],'string not int — different formats');
		$this->assertEqual($transaction_request->response['payload']['service_transaction_id'],'123abc');
		$this->assertEqual($transaction_request->response['payload']['data_sent'],'big JSON');
		$this->assertEqual($transaction_request->response['payload']['data_returned'],'also big JSON');
		$this->assertEqual($transaction_request->response['payload']['successful'],-1);
		$this->assertEqual($transaction_request->response['payload']['gross_price'],123.45);
		$this->assertEqual($transaction_request->response['payload']['service_fee'],12.34);
		
		$transaction_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'edittransaction',
				'id' => $this->testing_transaction,
				'successful' => 1,
				'data_returned' => json_encode(array('1','longer','thelongest'=>'goingforit'))
			)
		);
		$this->assertTrue($transaction_request->response['payload']);
		$transaction_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'gettransaction',
				'id' => $this->testing_transaction
			)
		);
		$this->assertEqual($transaction_request->response['payload']['successful'],1);
		$this->assertEqual($transaction_request->response['payload']['data_returned'],json_encode(array('1','longer','thelongest'=>'goingforit')));
	}
}

?>
