<?php
/**
 * Email For Download element
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class DigitalPurchase extends ElementBase {
	public $type = 'digitalpurchase';
	public $name = 'Digital Purchase';

	public function getData() {
		// define $markup to store all screen output
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'getitem',
				'id' => $this->options['item_id']
			)
		);
		$item = $item_request->response['payload'];
		$this->element_data['item_name'] = $item['name'];
		$this->element_data['item_price'] = number_format($item['price'], 2, '.', '');
		$this->element_data['item_flexible_price'] = $item['flexible_price'];
		$this->element_data['item_description'] = $item['description'];
		$this->element_data['item_asset'] = $item['fulfillment_asset'];

		if (
			$this->status_uid == 'commerce_finalizepayment_200' || 
			$this->status_uid == 'element_redeemcode_200' ||
			$this->status_uid == 'commerce_initiatecheckout_200' && $this->original_response['payload'] == 'force_success'
			) {
			if ($item['fulfillment_asset'] != 0) {
				$this->element_data['fulfillment_assets'] = array();
				// first we "unlock" the asset, telling the platform it's okay to generate a link for non-private assets
				$unlock_request = new CASHRequest(array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'unlock',
					'id' => $item['fulfillment_asset']
				));
				// next we make the link
				$asset_request = new CASHRequest(array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'getasset',
					'id' => $item['fulfillment_asset']
				));
				if ($asset_request->response['payload']['type'] != 'folder') {
					$this->element_data['fulfillment_assets'][] = array(
						'id' => $asset_request->response['payload']['id'],
						'title' => $asset_request->response['payload']['title'],
						'description' => $asset_request->response['payload']['description']
					);
				} else {
					$children_request = new CASHRequest(array(
						'cash_request_type' => 'asset', 
						'cash_action' => 'getassetsforparent',
						'parent_id' => $item['fulfillment_asset']
					));
					if (is_array($children_request->response['payload'])) {
						foreach ($children_request->response['payload'] as $child) {
							$this->element_data['fulfillment_assets'][] = array(
								'id' => $child['id'],
								'title' => $child['title'],
								'description' => $child['description']
							);
						}
					}
				}
				$this->setTemplate('success');
			}
		} elseif ($this->status_uid == 'commerce_initiatecheckout_400') {
			// could happen on a database glitch, but probably means the user set a pay-minimum price below the
			// minimum price. what a heel.
			$this->element_data['error_message'] = 'Make sure you enter a price of at least $' . $item['price'] . ' and try again.';
		} elseif ($this->status_uid == 'commerce_finalizepayment_400' || $this->status_uid == 'element_redeemcode_400') {
			// payerid is specific to paypal, so this is temporary to tell between canceled and errored:
			if (isset($_GET['PayerID'])) {
				$this->element_data['error_message'] = $this->options['message_error'];
			}
		}
		return $this->element_data;	
	}
} // END class 
?>