<?php
/**
 * Email For Download element
 *
 * @package emailcollection.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class EmailCollection extends ElementBase {
	public $type = 'emailcollection';
	public $name = 'Email Collection';

	public function getData() {
		if ($this->status_uid == 'people_signup_200' || $this->status_uid == 'people_verifyaddress_200') {
			$switch_case = 'final';
		} else {
			$switch_case = $this->status_uid;
		}
		switch ($switch_case) {
			case 'final':
				// successful submit, return messaging and optionally an asset link
				$show_final_message = true;
				if ($this->status_uid == 'people_signup_200' && !$this->options['do_not_verify']) {
					// if this is a first submit and we're verifying the email, first check to see if it's been verified already
					$verification_request = new CASHRequest(array(
						'cash_request_type' => 'people', 
						'cash_action' => 'checkverification',
						'address' => $this->original_response['payload']['address'],
						'list_id' => $this->options['email_list_id']
					));
					if (!$verification_request->response['payload']) {
						// not verified, so do not show the final message, and instead give a "you must verify" jam
						$show_final_message = false;
						$this->setTemplate('mustverify');
					}
				} 
				if ($show_final_message) {
					if ($this->options['asset_id'] != 0) {
						// get all fulfillment assets
						$fulfillment_request = new CASHRequest(
							array(
								'cash_request_type' => 'asset', 
								'cash_action' => 'getfulfillmentassets',
								'asset_details' => $this->options['asset_id']
							)
						);
						if ($fulfillment_request->response['payload']) {
							$this->element_data['fulfillment_assets'] = new ArrayIterator($fulfillment_request->response['payload']);
						}
					}
					$this->setTemplate('success');
				}
				break;
			case 'people_signup_400':
				$this->element_data['error_message'] = $this->options['message_invalid_email'];
				break;
		}
		return $this->element_data;
	}
} // END class 
?>