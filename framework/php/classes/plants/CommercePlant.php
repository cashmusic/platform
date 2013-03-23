<?php
/**
 * CommercePlant manages products/offers/orders, records transactions, and
 * deals with payment processors
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 * This file is generously sponsored by Devin Palmer | www.devinpalmer.com
 *
 **/
class CommercePlant extends PlantBase {
	
	public function __construct($request_type,$request) {
		$this->request_type = 'commerce';
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
			'getanalytics'     => array('getAnalytics','direct'),
			'getitem'          => array('getItem','direct'),
			'getitemsforuser'  => array('getItemsForUser','direct'),
			'getorder'         => array('getOrder','direct'),
			'getordersforuser' => array('getOrdersForUser','direct'),
			'gettransaction'   => array('getTransaction','direct'),
			'finalizepayment'  => array('finalizeRedirectedPayment',array('get','post','direct')),
			'initiatecheckout' => array('initiateCheckout',array('get','post','direct','api_public'))
		);
		$this->plantPrep($request_type,$request);
	}
	
	protected function addItem(
		$user_id,
		$name,
		$description='',
		$sku='',
		$price=0,
		$flexible_price=0,
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
				'flexible_price' => $flexible_price,
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
	
	protected function getItem($id,$user_id=false) {
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->getData(
			'items',
			'*',
			$condition
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
		$flexible_price=false,
		$available_units=false,
		$digital_fulfillment=false,
		$physical_fulfillment=false,
		$physical_weight=false,
		$physical_width=false,
		$physical_height=false,
		$physical_depth=false,
		$variable_pricing=false,
		$fulfillment_asset=false,
		$descriptive_asset=false,
		$user_id=false
	   ) {
		$final_edits = array_filter(
			array(
				'name' => $name,
				'description' => $description,
				'sku' => $sku,
				'price' => $price,
				'flexible_price' => $flexible_price,
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
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->setData(
			'items',
			$final_edits,
			$condition
		);
		return $result;
	}
	
	protected function deleteItem($id,$user_id=false) {
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->deleteData(
			'items',
			$condition
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
	
	protected function getOrder($id,$deep=false,$user_id=false) {
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
				if ($user_id) {
					if ($result[0]['user_id'] != $user_id) {
						return false;
					}
				}
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
			$condition = array(
				"id" => array(
					"condition" => "=",
					"value" => $id
				)
			);
			if ($user_id) {
				$condition['user_id'] = array(
					"condition" => "=",
					"value" => $user_id
				);
			}
			$result = $this->db->getData(
				'orders',
				'*',
				$condition
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
		$digital=false,
		$user_id=false
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
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->setData(
			'orders',
			$final_edits,
			$condition
		);
		return $result;
	}

	protected function getOrdersForUser($user_id,$include_abandoned=false,$max_returned=false) {
		if ($max_returned) {
			$limit = '0, ' . $max_returned;
		} else {
			$limit = false;
		}
		$conditions = array(
			"user_id" => array(
				"condition" => "=",
				"value" => $user_id
			)
		);
		if (!$include_abandoned) {
			$conditions['modification_date'] = array(
				"condition" => ">",
				"value" => 0
			);
		}
		$result = $this->db->getData(
			'orders',
			'*',
			$conditions,
			$limit,
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
	
	protected function getTransaction($id,$user_id=false) {
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->getData(
			'transactions',
			'*',
			$condition
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
	
	protected function initiateCheckout($user_id,$connection_id,$order_contents=false,$item_id=false,$element_id=false,$total_price=false,$return_url_only=false) {
		if (!$order_contents && !$item_id) {
			return false;
		} else {
			if (!$order_contents) {
				$order_contents = array();
			}
			if ($item_id) {
				$item_details = $this->getItem($item_id);
				$order_contents[] = $item_details;
				if ($total_price !== false && $total_price >= $item_details['price']) {
					$price_addition = $total_price - $item_details['price'];
				} elseif ($total_price === false) {
					$price_addition = 0;
				} else {
					return false;
				}
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
				$this->getSessionID(),
				$element_id
			);
			if ($order_id) {
				$success = $this->initiatePaymentRedirect($order_id,$element_id,$price_addition,$return_url_only);
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
	
	protected function initiatePaymentRedirect($order_id,$element_id=false,$price_addition=0,$return_url_only=false) {
		$order_details = $this->getOrder($order_id);
		$transaction_details = $this->getTransaction($order_details['transaction_id']);
		$order_totals = $this->getOrderTotals($order_details['order_contents']);
		$connection_type = $this->getConnectionType($transaction_details['connection_id']);
		if (($order_totals['price'] + $price_addition) < 0.35) {
			// basically a zero dollar transaction. hard-coding a 35¢ minimum for now
			// we can add a system minimum later, or a per-connection minimum, etc...
			return 'force_success';
		}
		switch ($connection_type) {
			case 'com.paypal':
				$pp = new PaypalSeed($order_details['user_id'],$transaction_details['connection_id']);
				$return_url = CASHSystem::getCurrentURL() . '?cash_request_type=commerce&cash_action=finalizepayment&order_id=' . $order_id . '&creation_date=' . $order_details['creation_date'];
				if ($element_id) {
					$return_url .= '&element_id=' . $element_id;
				}
				$redirect_url = $pp->setExpressCheckout(
					$order_totals['price'] + $price_addition,
					'order-' . $order_id,
					$order_totals['description'],
					$return_url,
					$return_url
				);
				if (!$return_url_only) {
					$redirect = CASHSystem::redirectToUrl($redirect_url);
					// the return will only happen if headers have already been sent
					// if they haven't redirectToUrl() will handle it and call exit
					return $redirect;
				} else {
					return $redirect_url;
				}
				break;
			default:
				return false;
		}
		return false;
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
										strtotime($final_details['TIMESTAMP']),
										$final_details['CORRELATIONID'],
										json_encode($initial_details),
										json_encode($final_details),
										1,
										$final_details['PAYMENTINFO_0_AMT'],
										$final_details['PAYMENTINFO_0_FEEAMT'],
										'complete'
									);
									$addcode_request = new CASHRequest(
										array(
											'cash_request_type' => 'element', 
											'cash_action' => 'addlockcode',
											'element_id' => $order_details['element_id']
										)
									);
									// TODO: add code to order metadata
									// bit of a hack, hard-wiring the email bits:
									try {
										CASHSystem::sendEmail(
											'Your download is ready',
											$order_details['user_id'],
											$initial_details['EMAIL'],
											'Your download of "' . $initial_details['PAYMENTREQUEST_0_DESC'] . '" is ready and can be found at: '
											. CASHSystem::getCurrentURL() . '?cash_request_type=element&cash_action=redeemcode&code=' . $addcode_request->response['payload']
											. '&element_id=' . $order_details['element_id'] . '&email=' . urlencode($initial_details['EMAIL']),
											'Thank you'
										);
									} catch (Exception $e) {
										// TODO: handle the case where an email can't be sent. maybe display the download
										//       code on-screen? that plus storing it with the order is probably enough
									}
									return true;
								} else {
									// make sure this isn't an accidentally refreshed page
									if ($initial_details['CHECKOUTSTATUS'] != 'PaymentActionCompleted'){
										$initial_details['ERROR_MESSAGE'] = $pp->getErrorMessage();
										// there was an error processing the transaction
										$this->editOrder(
											$order_id,
											0,
											1
										);
										$this->editTransaction(
											$order_details['transaction_id'],
											strtotime($initial_details['TIMESTAMP']),
											$initial_details['CORRELATIONID'],
											false,
											json_encode($initial_details),
											0,
											false,
											false,
											'error processing payment'
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
									strtotime($initial_details['TIMESTAMP']),
									$initial_details['CORRELATIONID'],
									false,
									json_encode($initial_details),
									0,
									false,
									false,
									'incorrect amount'
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
								strtotime($initial_details['TIMESTAMP']),
								$initial_details['CORRELATIONID'],
								false,
								json_encode($initial_details),
								0,
								false,
								false,
								'payment failed'
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
							time(),
							false,
							false,
							false,
							0,
							false,
							false,
							'canceled'
						);
						return false;
					}
				}
				break;
			default:
				return false;
		}
	}

	/**
	 * Pulls analytics queries in a few different formats
	 *
	 * @return array
	 */protected function getAnalytics($analtyics_type,$user_id,$date_low=false,$date_high=false) {
		//
		// left a commented-out switch so we can easily add more cases...
		//
		//switch (strtolower($analtyics_type)) {
		//	case 'transactions':
				if (!$date_low) $date_low = 201243600;
				if (!$date_high) $date_high = time();
				$result = $this->db->getData(
					'CommercePlant_getAnalytics_transactions',
					false,
					array(
						"user_id" => array(
							"condition" => "=",
							"value" => $user_id
						),
						"date_low" => array(
							"condition" => "=",
							"value" => $date_low
						),
						"date_high" => array(
							"condition" => "=",
							"value" => $date_high
						)
					)
				);
				if ($result) {
					return $result[0];
				} else {
					return $result;
				}
		//		break;
		//}
	}
	
} // END class 
?>