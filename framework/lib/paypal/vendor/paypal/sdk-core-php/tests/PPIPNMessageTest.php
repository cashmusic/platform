<?php
use PayPal\IPN\PPIPNMessage;
use PayPal\Core\PPConstants;
/**
 * Test class for PPIPNMessage.
 *
 */
class PPIPNMessageTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	
	public function passGoodIPN() {
		
	}
	
	/**
	 * @test
	 */
	public function testIPNWithCustomConfig() {
		$ipnData = "id=123&item=oreo's";
		$ipn = new PPIPNMessage($ipnData, array('mode' => 'sandbox'));
		$this->assertEquals(false, $ipn->validate());

		$ipnData = "id=123&item=oreo's";
		$ipn = new PPIPNMessage($ipnData, array('mode' => 'live'));
		$this->assertEquals(false, $ipn->validate());

		$ipnData = "id=123&item=oreo's";
		$ipn = new PPIPNMessage($ipnData, array('service.EndPoint.IPN' => PPConstants::IPN_SANDBOX_ENDPOINT));
		$this->assertEquals(false, $ipn->validate());

		$this->setExpectedException('PayPal\Exception\PPConfigurationException');
		$ipn = new PPIPNMessage($ipnData, array('mode' => 'invalid'));
		$ipn->validate();

	}

	/**
	 * @test
	 */
	public function testGetTransactionData() {

		$ipnData = "txn_data=notavailable";
		$ipn = new PPIPNMessage($ipnData, array('mode' => 'sandbox'));
		$this->assertEquals('', $ipn->getTransactionId());

		$ipnData = "txn_id=123&transaction_type=pay";
		$ipn = new PPIPNMessage($ipnData, array('mode' => 'sandbox'));
		$this->assertEquals(123, $ipn->getTransactionId());
		$this->assertEquals('pay', $ipn->getTransactionType());

		$ipnData = "transaction[0].id=5&transaction[1].id=10";
		$ipn = new PPIPNMessage($ipnData, array('mode' => 'sandbox'));
		$this->assertEquals(array(5,10), $ipn->getTransactionId());

		$ipnData = "txn_id=123&transaction[0].id=5&transaction[1].id=10";
		$ipn = new PPIPNMessage($ipnData, array('mode' => 'sandbox'));
		$this->assertEquals(123, $ipn->getTransactionId());
	}
	
	/**
	 * @test
	 */
	public function failOnBadIPN() {
		$ipn = new PPIPNMessage();
		$this->assertEquals(false, $ipn->validate());
	}
	
	
	/**
	 * @test
	 */
	public function processIPNWithArrayElements() {
		$ipnData = 'transaction[0].id=6WM123443434&transaction[0].status=Completed&transaction[1].id=2F12129812A1&transaction[1].status=Pending';
		$ipn = new PPIPNMessage($ipnData);
		
		$rawData = $ipn->getRawData();
		$this->assertEquals(4, count($rawData));
		$this->assertEquals('6WM123443434', $rawData['transaction[0].id']);
	}
	
	/**
	 * @test
	 */	
	public function processIPNWithSpecialCharacters() {
		$ipnData = "description=Jake's store";
		
		ini_set('get_magic_quotes_gpc', true);
		$ipn = new PPIPNMessage($ipnData);
		$rawData = $ipn->getRawData();		
		$this->assertEquals($rawData['description'], "Jake's store");
		
		ini_set('get_magic_quotes_gpc', false);
		$ipn = new PPIPNMessage($ipnData);
		$rawData = $ipn->getRawData();
		$this->assertEquals($rawData['description'], "Jake's store");
		$this->assertEquals($rawData['description'], "Jake's store");
	}
	
}
