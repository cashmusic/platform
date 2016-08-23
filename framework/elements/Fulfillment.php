<?php
/**
 * Email For Download element
 *
 * @package downloadcodes.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class Fulfillment extends ElementBase {
	public $type = 'fulfillment';
	public $name = '3rd Party Fulfillment';

	public function getData() {
		$this->element_data['element_id'] = $this->element_id;
		$this->element_data['public_url'] = CASH_PUBLIC_URL;

		// handle any rename stuff
		if (isset($_REQUEST['setlocation'])) {
			if ($_REQUEST['order_id'] && $_REQUEST['postal_code'] && $_REQUEST['country']) {
				// set location for job
				$order_request = new CASHRequest(
					array(
						'cash_request_type' => 'commerce',
						'cash_action' => 'editfulfillmentorder',
						'id' => $_REQUEST['order_id'],
						'shipping_country' => $_REQUEST['country'],
						'shipping_postal' => $_REQUEST['postal_code']
					)
				);
			} else {
				// not enough info. try again.
				$this->element_data['error_message'] = true;
			}
		}

		if (isset($_REQUEST['code'])) {
			// this means someone just clicked the link to the element. set the code
			// and don't worry beyond that
			$this->element_data['code'] = $_REQUEST['code'];
		} elseif (isset($_REQUEST['processcode'])) {
			// get/set the basics
			$locationknown = false;
			$this->element_data['code'] = $_REQUEST['processcode'];
			$this->element_data['fulfillment_order_id'] = 0;

			// the code has been given and now we're in the overlay.
			$code_request = new CASHRequest(
				array(
					'cash_request_type' => 'system',
					'cash_action' => 'redeemlockcode',
					'code' => $this->element_data['code']
				)
			);
			if ($code_request->response['payload']) {
				if ($code_request->response['payload']['scope_table_alias'] == 'external_fulfillment_orders') {
					$this->element_data['fulfillment_order_id'] = $code_request->response['payload']['scope_table_id'];
				}
			}

			// now we either have a fulfillment_order_id or the code has been cashed in already or is invalid
			if ($this->element_data['fulfillment_order_id']) {
				// we have an order. let's get the details and see if we know location
				$order_request = new CASHRequest(
					array(
						'cash_request_type' => 'commerce',
						'cash_action' => 'getfulfillmentorder',
						'id' => $this->element_data['fulfillment_order_id']
					)
				);
				if ($order_request->response['payload']) {
					if ($order_request->response['payload']['shipping_country'] && $order_request->response['payload']['shipping_postal']) {
						$locationknown = true;
					}
				}

				if ($locationknown) {
					// we know where the person is (or where they shipped things to) so
					// now it's time to unlock the asset and show the success template
					// with the correct asset id
					$this->element_data['asset_id'] = 0;
					$job_request = new CASHRequest(
						array(
							'cash_request_type' => 'commerce',
							'cash_action' => 'getfulfillmentjobbytier',
							'id' => $order_request->response['payload']['tier_id']
						)
					);
					if ($job_request->response['payload']) {
						$this->element_data['asset_id'] = $job_request->response['asset_id'];
					}

					 // TODO getAssetForFulfillmentOrder
					if ($this->element_data['asset_id'] != 0) {
						// get all fulfillment assets
						$fulfillment_request = new CASHRequest(
							array(
								'cash_request_type' => 'asset',
								'cash_action' => 'getfulfillmentassets',
								'asset_details' => $this->element_data['asset_id'],
								'session_id' => $this->session_id
							)
						);
						if ($fulfillment_request->response['payload']) {
							$this->element_data['fulfillment_assets'] = new ArrayIterator($fulfillment_request->response['payload']);
						}
					}
					// mark the order as complete with a timestamp
					$order_request = new CASHRequest(
						array(
							'cash_request_type' => 'commerce',
							'cash_action' => 'editfulfillmentorder',
							'id' => $_REQUEST['order_id'],
							'complete' => time()
						)
					);
					$this->setTemplate('success');
				} else {
					// we don't know the location so let's ask
					$this->setTemplate('location');
				}
			} else {
				// the code is already used/invalidate
				$this->setTemplate('invalid');
			}
		}

		return $this->element_data;
	}
} // END class
?>
