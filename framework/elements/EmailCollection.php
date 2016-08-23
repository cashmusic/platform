<?php
/**
 * Email For Download element
 *
 * @package emailcollection.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 *
 * This file is generously sponsored by This file sponsored by Balthrop, Alabama
 * Balthrop, Alabama (http://www.ballthropalabama.com) and End Up Records (http://endup.org).
 * Go CASH Music!
 *
 **/
class EmailCollection extends ElementBase {
	public $type = 'emailcollection';
	public $name = 'Email Collection';

	public function getData() {
		if (isset($_REQUEST['geo'])) {
			$this->element_data['geo'] = $_REQUEST['geo'];
		}
		if (isset($_REQUEST['redirecterror'])) {
			$this->element_data['redirecterror'] = true;
		}
		if (isset($this->element_data['agree_message'])) {
			$this->element_data['agree_message'] = str_replace("'","\'",$this->element_data['agree_message']);
		}
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
						'address' => $this->original_request['address'],
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
								'asset_details' => $this->options['asset_id'],
								'session_id' => $this->session_id
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
