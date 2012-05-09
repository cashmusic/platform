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
class DownloadCodes extends ElementBase {
	public $type = 'downloadcodes';
	public $name = 'Download Codes';

	public function getData() {
		if ($this->status_uid == 'asset_redeemcode_400') {
			$this->element_data['error_message'] = 'That code is not valid or has already been used.';
		} elseif ($this->status_uid == 'asset_redeemcode_200') {
			// first we "unlock" the asset, telling the platform it's okay to generate a link for non-private assets
			$this->element_data['asset_id'] = $this->original_response['payload']['scope_table_id'];
			$unlock_request = new CASHRequest(array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'unlock',
				'id' => $this->element_data['asset_id']
			));
			// next we make the link
			$asset_request = new CASHRequest(array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'getasset',
				'id' => $this->element_data['asset_id']
			));
			$this->element_data['asset_title'] = $asset_request->response['payload']['title'];
			$this->element_data['asset_description'] = $asset_request->response['payload']['description'];
			$this->setTemplate('success');
		}
		return $this->element_data;
	}
} // END class 
?>