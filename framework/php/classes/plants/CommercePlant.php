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
				'signintolist'       => array('validateUserForList',array('post','direct','api_key')),
				'verifyaddress'      => array('doAddressVerification','direct'),
			);
			// see if the action matches the routing table:
			$basic_routing = $this->routeBasicRequest();
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
	
	protected function addItem() {}
	
	protected function getItem() {}
	
	protected function editItem() {}
	
	protected function deleteItem() {}
	
	protected function addOrder() {}
	
	protected function getOrder() {}
	
	protected function editOrder() {}
	
	protected function addTransaction() {}
	
	protected function getTransaction() {}
	
	protected function initiatePaymentRedirect() {}
	
	protected function finaliztRedirectedPayment() {}
	
} // END class 




/*
include('../../cashmusic.php');

$paypal = new PaypalSeed(1,14);
$redirect_url = $paypal->setExpressCheckout(
	'13.26',
	'order_sku',
	'this is the best order ever',
	'http://localhost',
	'http://localhost'
);

//$redirect = CASHSystem::redirectToUrl($redirect_url);
//echo $redirect;
*/
?>