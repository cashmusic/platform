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
		$this->element_data['item_price'] = $item['price'];
		$this->element_data['item_description'] = $item['description'];
		$this->element_data['item_asset'] = $item['fulfillment_asset'];

		if ($this->status_uid == 'commerce_finalizepayment_200' || $this->status_uid == 'element_redeemcode_200') {
			if ($item['fulfillment_asset'] != 0) {
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
				$this->element_data['asset_title'] = $asset_request->response['payload']['title'];
				$this->element_data['asset_description'] = $asset_request->response['payload']['description'];
				$this->setTemplate('success');
			}
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