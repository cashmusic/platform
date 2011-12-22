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
	const type = 'ecard';
	const name = 'E-Card';

	public function getMarkup() {
		// define $markup to store all screen output
		$markup = '';
		// the default form and basic elements:
		$default_markup = '<p class="cash_element_intro">' . $this->options->message_instructions . '</p>';
		if (!empty($this->options->image_url)) {
			$default_markup .= '<img src="'. $this->options->image_url . '" alt="E-Card" class="cash_image_ecard" />';
		}
		$default_markup .= '<form id="cash_'. self::type .'_form_' . $this->element_id . '" class="cash_form '. self::type .'" method="post" action="">'
			. '<div class="cash_main_name_container"><label for="address">Your Name: (The \'from\' for the card)</label>'
			. '<input type="text" name="main_name" value="" class="cash_input cash_input_address cash_main_name" /></div>'
			. '<div class="cash_main_address_container"><label for="address">Your Email:</label>'
			. '<input type="email" name="address" value="" class="cash_input cash_input_address cash_main_address" /></div>'
			. '<div class="cash_friends">'
			. '<label for="address">Email Addresses For Up To 3 Friends:</label>'
			. '<input type="email" name="friend1" value="" class="cash_input cash_input_address cash_friend_address1" />'
			. '<input type="email" name="friend2" value="" class="cash_input cash_input_address cash_friend_address2" />'
			. '<input type="email" name="friend3" value="" class="cash_input cash_input_address cash_friend_address3" />'
			. '</div>'
			. '<input type="hidden" name="cash_request_type" value="people" />'
			. '<input type="hidden" name="cash_action" value="signup" />'
			. '<input type="hidden" name="list_id" value="'.$this->options->emal_list_id.'" class="cash_input cash_input_list_id" />'
			. '<input type="hidden" name="element_id" value="'.$this->element_id.'" class="cash_input cash_input_element_id" />'
			. '<input type="hidden" name="comment" value="" class="cash_input cash_input_comment" />'
			. '<input type="submit" value="send the cards" class="button" /><br />'
			. '</form>';
		switch ($this->status_uid) {
			case 'people_signup_200' || 'people_verifyaddress_200':
				// successful submit, return messaging and optionally an asset link
				$markup = '<div class="cash_success '. self::type .'">';
				$show_final_message = true;
				if ($this->status_uid == 'people_signup_200' && !$this->options->do_not_verify) {
					// if this is a first submit and we're verifying the email, first check to see if it's been verified already
					$verification_request = new CASHRequest(array(
						'cash_request_type' => 'people', 
						'cash_action' => 'checkverification',
						'address' => $this->original_request->response['payload']['address'],
						'list_id' => $this->options->emal_list_id
					));
					if (!$verification_request->response['payload']) {
						// not verified, so do not show the final message, and instead give a "you must verify" jam
						$show_final_message = false;
						$markup .= 'You must verify your email address to continue. An email has been sent. Click the link provided and you will be brought back here.<br /><br />(If you do not see the message, check your SPAM folder.)';
					}
				} 
				if ($show_final_message) {
					
					$all_friends = array($this->original_request->request['friend1'],$this->original_request->request['friend2'],$this->original_request->request['friend3']);
					if (!empty($this->original_request->request['main_name'])) {
						$from_name = $this->original_request->request['main_name'];
					} else {
						$from_name = $this->original_request->request['address'];
					}
					if (!empty($this->options->email_html_message)) {
						$html_message = str_replace('</body>','<br /><br /><br /><small>This e-card was sent from <a href="' . CASHSystem::getCurrentURL() . '">' . CASHSystem::getCurrentURL() . '</a></small></body>',$this->options->email_html_message);
					} else {
						$html_message = false;
					}
					foreach($all_friends as $friend) {
						if (filter_var($friend, FILTER_VALIDATE_EMAIL)) {
							CASHSystem::sendEmail(
								trim($this->options->email_subject) . ' ' . $from_name,
								CASHSystem::getDefaultEmail(),
								$friend,
								$this->options->email_message . "\n\n\nThis e-card was sent from " . CASHSystem::getCurrentURL(),
								'',
								$html_message
							);
						}
					}
					
					$markup .= $this->options->message_success;
					if ($this->options->asset_id != 0) {
						// first we "unlock" the asset, telling the platform it's okay to generate a link for non-private assets
						$unlock_request = new CASHRequest(array(
							'cash_request_type' => 'asset', 
							'cash_action' => 'unlock',
							'id' => $this->options->asset_id
						));
						// next we make the link
						$asset_request = new CASHRequest(array(
							'cash_request_type' => 'asset', 
							'cash_action' => 'getasset',
							'id' => $this->options->asset_id
						));
						$asset_title = $asset_request->response['payload']['title'];
						$asset_description = $asset_request->response['payload']['description'];
						$markup .= '<br /><br />'
						. '<a href="?cash_request_type=asset&cash_action=claim&id='.$this->options->asset_id.'&element_id='.$this->element_id.'" class="download">'. $asset_title .'</a>'
						. '<div class="description">' . $asset_description . '</div>';
					}
				}
				if (!empty($this->options->image_url)) {
					$markup .= '<img src="'. $this->options->image_url . '" alt="E-Card" class="cash_image_ecard" />';
				}
				$markup .= '</div>';
				break;
			case 'people_signup_400':
				// error, likely in the email format. error message + default form
				$markup = '<div class="cash_error '. self::type .'">'
				. $this->options->message_invalid_email
				. '</div>'
				. $default_markup;
				break;
			default:
				// default form
				$markup = $default_markup;
		}
		return $markup;	
	}
} // END class 
?>