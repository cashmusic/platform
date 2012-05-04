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
class SecureDownload extends ElementBase {
	public $type = 'securedownload';
	public $name = 'Secure Download';

	public function getData() {
		if ($this->options['skip_login']) {
			$show_final_message = true;
		} else {
			$show_final_message = false;
			if ($this->status_uid == 'people_signintolist_200') {
				$show_final_message = true;
			} elseif ($this->status_uid == 'people_signintolist_400') {
				// sign-in failed, try element-specific password and check that the 
				// address is for realy realz on the list
				if (trim($this->original_request['password']) == trim($this->options['alternate_password'])) {
					$status_request = new CASHRequest(array(
						'cash_request_type' => 'people', 
						'cash_action' => 'getaddresslistinfo',
						'address' => $this->original_request['address'],
						'list_id' => $this->options['email_list_id']
					));
					if ($status_request->response['payload']) {
						$show_final_message = true;
					}
				}
			}
		}
		if ($show_final_message) {
			if ($this->options['asset_id'] != 0) {
				// first we "unlock" the asset, telling the platform it's okay to generate a link for non-private assets
				$unlock_request = new CASHRequest(array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'unlock',
					'id' => $this->options['asset_id']
				));
				// next we make the link
				$asset_request = new CASHRequest(array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'getasset',
					'id' => $this->options['asset_id']
				));
				$this->element_data['asset_title'] = $asset_request->response['payload']['title'];
				$this->element_data['asset_description'] = $asset_request->response['payload']['description'];
			}
			$this->setTemplate('success');
		}
		return $this->element_data;
	}
} // END class 
?>