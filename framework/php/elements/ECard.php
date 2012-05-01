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
class ECard extends ElementBase {
	public $type = 'ecard';
	public $name = 'E-Card';

	public function getData() {
		switch ($this->status_uid) {
			case 'people_signup_200' || 'people_verifyaddress_200':
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
					$all_friends = array($this->original_request['friend1'],$this->original_request['friend2'],$this->original_request['friend3']);
					if (!empty($this->original_request['main_name'])) {
						$from_name = $this->original_request['main_name'];
					} else {
						$from_name = $this->original_request['address'];
					}
					if (!empty($this->options['email_html_message'])) {
						$html_message = str_replace('</body>','<br /><br /><br /><small>This e-card was sent from <a href="' . CASHSystem::getCurrentURL() . '">' . CASHSystem::getCurrentURL() . '</a></small></body>',$this->options['email_html_message']);
					} else {
						$html_message = false;
					}
					foreach($all_friends as $friend) {
						if (filter_var($friend, FILTER_VALIDATE_EMAIL)) {
							CASHSystem::sendEmail(
								trim($this->options['email_subject']) . ' ' . $from_name,
								CASHSystem::getDefaultEmail(),
								$friend,
								$this->options['email_message'] . "\n\n\nThis e-card was sent from " . CASHSystem::getCurrentURL(),
								'',
								$html_message
							);
						}
					}
					
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
				break;
			case 'people_signup_400':
				// error, likely in the email format. error message + default form
				$this->element_data['error_message'] = $this->options['message_invalid_email'];
				break;
		}
		return $this->element_data;	
	}
} // END class 
?>