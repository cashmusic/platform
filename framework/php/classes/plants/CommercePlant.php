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
				'getordersforuser' => array('getOrdersForUser','direct'),
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
		$transaction_id=-1,
		$physical=0,
		$digital=0,
		$cash_session_id='',
		$element_id=0,
		$customer_user_id=0,
		$fulfilled=0,
		$canceled=0,
		$notes='',
		$country_code=''
	) {
		if (is_array($order_contents)) {
			/*
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
					'canceled' => $canceled,
					'physical' => $physical,
					'digital' => $digital,
					'notes' => $notes,
					'country_code' => $country_code,
					'element_id' => $element_id,
					'cash_session_id' => $cash_session_id
				)
			);
			return $result;
		} else {
			return false;
		}
	}
	
	protected function getOrder($id,$deep=false) {
		if ($deep) {
			$result = $this->db->getData(
				'CommercePlant_getOrder_deep',
				false,
				array(
					"id" => array(
						"condition" => "=",
						"value" => $id
					)
				)
			);
			if ($result) {
				$result[0]['order_totals'] = $this->getOrderTotals($result[0]['order_contents']);
				$user_request = new CASHRequest(
					array(
						'cash_request_type' => 'people', 
						'cash_action' => 'getuser',
						'user_id' => $result[0]['customer_user_id']
					)
				);
				$result[0]['customer_details'] = $user_request->response['payload'];
			}
		} else {
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
		}
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}
	
	protected function editOrder(
		$id,
		$fulfilled=false,
		$canceled=false,
		$notes=false,
		$country_code=false,
		$customer_user_id=false,
		$order_contents=false,
		$transaction_id=false,
		$physical=false,
		$digital=false
	) {
		if ($order_contents) {
			$order_contents = json_encode($order_contents);
		}
		$final_edits = array_filter(
			array(
				'transaction_id' => $transaction_id,
				'order_contents' => $order_contents,
				'fulfilled' => $fulfilled,
				'canceled' => $canceled,
				'physical' => $physical,
				'digital' => $digital,
				'notes' => $notes,
				'country_code' => $country_code,
				'customer_user_id' => $customer_user_id
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

	protected function getOrdersForUser($user_id) {
		$result = $this->db->getData(
			'orders',
			'*',
			array(
				"user_id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			),
			false,
			'id DESC'
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
		$service_fee=0,
		$status='abandoned'
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
				'service_fee' => $service_fee,
				'status' => $status
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
		$service_timestamp=false,
		$service_transaction_id=false,
		$data_sent=false,
		$data_returned=false,
		$successful=false,
		$gross_price=false,
		$service_fee=false,
		$status=false
	) {
		$final_edits = array_filter(
			array(
				'service_timestamp' => $service_timestamp,
				'service_transaction_id' => $service_transaction_id,
				'data_sent' => $data_sent,
				'data_returned' => $data_returned,
				'successful' => $successful,
				'gross_price' => $gross_price,
				'service_fee' => $service_fee,
				'status' => $status
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
	
	protected function initiateCheckout($user_id,$connection_id,$order_contents=false,$item_id=false,$element_id=false) {
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
				$this->getConnectionType($connection_id)
			);
			$order_id = $this->addOrder(
				$user_id,
				$order_contents,
				$transaction_id,
				0,
				1,
				$this->getCASHSessionID(),
				$element_id
			);
			if ($order_id) {
				$success = $this->initiatePaymentRedirect($order_id,$element_id);
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
		$return_array['description'] = rtrim($return_array['description']);
		return $return_array;
	}
	
	protected function initiatePaymentRedirect($order_id,$element_id=false) {
		$order_details = $this->getOrder($order_id);
		$transaction_details = $this->getTransaction($order_details['transaction_id']);
		$order_totals = $this->getOrderTotals($order_details['order_contents']);
		$connection_type = $this->getConnectionType($transaction_details['connection_id']);
		switch ($connection_type) {
			case 'com.paypal':
				$pp = new PaypalSeed($order_details['user_id'],$transaction_details['connection_id']);
				$return_url = CASHSystem::getCurrentURL() . '?cash_request_type=commerce&cash_action=finalizepayment&order_id=' . $order_id . '&creation_date=' . $order_details['creation_date'];
				if ($element_id) {
					$return_url .= '&element_id=' . $element_id;
				}
				$redirect_url = $pp->setExpressCheckout(
					$order_totals['price'],
					'order-' . $order_id,
					$order_totals['description'],
					$return_url,
					$return_url
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
	
	protected function finalizeRedirectedPayment($order_id,$creation_date,$direct_post_details=false) {
		$order_details = $this->getOrder($order_id);
		$transaction_details = $this->getTransaction($order_details['transaction_id']);
		$connection_type = $this->getConnectionType($transaction_details['connection_id']);
		switch ($connection_type) {
			case 'com.paypal':
				if (isset($_GET['token'])) {
					if (isset($_GET['PayerID'])) {
						$pp = new PaypalSeed($order_details['user_id'],$transaction_details['connection_id'],$_GET['token']);
						$initial_details = $pp->getExpressCheckout();
						if ($initial_details['ACK'] == 'Success') {
							$order_totals = $this->getOrderTotals($order_details['order_contents']);
							if ($initial_details['AMT'] >= $order_totals['price']) {
								$final_details = $pp->doExpressCheckout();
								if ($final_details) {
									// look for a user to match the email. if not present, make one
									$user_request = new CASHRequest(
										array(
											'cash_request_type' => 'people', 
											'cash_action' => 'getuseridforaddress',
											'address' => $initial_details['EMAIL']
										)
									);
									$user_id = $user_request->response['payload'];
									if (!$user_id) {
										$user_request = new CASHRequest(
											array(
												'cash_request_type' => 'system', 
												'cash_action' => 'addlogin',
												'address' => $initial_details['EMAIL'], 
												'password' => time(),
												'is_admin' => 0,
												'display_name' => $initial_details['FIRSTNAME'] . ' ' . $initial_details['LASTNAME'],
												'first_name' => $initial_details['FIRSTNAME'],
												'last_name' => $initial_details['LASTNAME'],
												'address_country' => $initial_details['COUNTRYCODE']
											)
										);
										$user_id = $user_request->response['payload'];
									}
									
									// record the details to the order/transaction where appropriate
									$this->editOrder(
										$order_id,
										1,
										0,
										false,
										$initial_details['COUNTRYCODE'],
										$user_id
									);
									$this->editTransaction(
										$order_details['transaction_id'],
										$service_timestamp=strtotime($final_details['TIMESTAMP']),
										$service_transaction_id=$final_details['CORRELATIONID'],
										$data_sent=json_encode($initial_details),
										$data_returned=json_encode($final_details),
										$successful=1,
										$gross_price=$final_details['PAYMENTINFO_0_AMT'],
										$service_fee=$final_details['PAYMENTINFO_0_FEEAMT'],
										$status='complete'
									);
									$addcode_request = new CASHRequest(
										array(
											'cash_request_type' => 'element', 
											'cash_action' => 'addlockcode',
											'element_id' => $order_details['element_id']
										)
									);
									// bit of a hack, hard-wiring the email bits:
									CASHSystem::sendEmail(
										'Your download is ready',
										CASHSystem::getDefaultEmail(),
										$initial_details['EMAIL'],
										'Your download of "' . $initial_details['L_PAYMENTREQUEST_0_NAME0'] . '" is ready and can be found at: '
										. CASHSystem::getCurrentURL() . '?cash_request_type=element&cash_action=redeemcode&code=' . $addcode_request->response['payload']
										. '&element_id=' . $order_details['element_id'] . '&email=' . urlencode($initial_details['EMAIL']),
										'Thank you'
									);
									
									return true;
								} else {
									// make sure this isn't an accidentally refreshed page
									if ($initial_details['CHECKOUTSTATUS'] != 'PaymentActionCompleted'){
										$initial_details['ERROR_MESSAGE'] = $pp->getErrorMessage();
										// there was an error processing the transaction
										var_dump();
										$this->editOrder(
											$order_id,
											0,
											1
										);
										$this->editTransaction(
											$order_details['transaction_id'],
											$service_timestamp=strtotime($initial_details['TIMESTAMP']),
											$service_transaction_id=$initial_details['CORRELATIONID'],
											$data_sent=false,
											$data_returned=json_encode($initial_details),
											$successful=0,
											$gross_price=false,
											$service_fee=false,
											$status='error processing payment'
										);
										return false;
									} else {
										// this is a successful transaction with the user hitting refresh
										// as long as it's within 30 minutes of the original return true, otherwise
										// call it false and allow the page to expire
										if (time() - strtotime($initial_details['TIMESTAMP']) < 180) {
											return true;
										} else {
											return false;
										}
									}
								}
							} else {
								// insufficient funds — user changed amount?
								$this->editOrder(
									$order_id,
									0,
									1
								);
								$this->editTransaction(
									$order_details['transaction_id'],
									$service_timestamp=strtotime($initial_details['TIMESTAMP']),
									$service_transaction_id=$initial_details['CORRELATIONID'],
									$data_sent=false,
									$data_returned=json_encode($initial_details),
									$successful=0,
									$gross_price=false,
									$service_fee=false,
									$status='incorrect amount'
								);
								return false;
							}
						} else {
							// order reporting failure
							$this->editOrder(
								$order_id,
								0,
								1
							);
							$this->editTransaction(
								$order_details['transaction_id'],
								$service_timestamp=strtotime($initial_details['TIMESTAMP']),
								$service_transaction_id=$initial_details['CORRELATIONID'],
								$data_sent=false,
								$data_returned=json_encode($initial_details),
								$successful=0,
								$gross_price=false,
								$service_fee=false,
								$status='payment failed'
							);
							return false;
						}
					} else {
						// user canceled transaction
						$this->editOrder(
							$order_id,
							0,
							1
						);
						$this->editTransaction(
							$order_details['transaction_id'],
							$service_timestamp=time(),
							$service_transaction_id=false,
							$data_sent=false,
							$data_returned=false,
							$successful=0,
							$gross_price=false,
							$service_fee=false,
							$status='canceled'
						);
						return false;
					}
				}
				break;
			default:
				return false;
		}
	}
	
} // END class 
?>