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
class EmailCollection extends ElementBase {
	const type = 'emailcollection';
	const name = 'Email Collection';

	public function getMarkup() {
		// define $markup to store all screen output
		$markup = '';
		// the default form and basic elements:
		$default_markup = '<form id="cash_'. self::type .'_form_' . $this->element_id . '" class="cash_form '. self::type .'" method="post" action="">'
			. '<input type="email" name="address" value="" class="cash_input cash_input_address" />'
			. '<input type="hidden" name="cash_request_type" value="people" />'
			. '<input type="hidden" name="cash_action" value="signup" />'
			. '<input type="hidden" name="list_id" value="'.$this->options->email_list_id.'" class="cash_input cash_input_list_id" />'
			. '<input type="hidden" name="element_id" value="'.$this->element_id.'" class="cash_input cash_input_element_id" />'
			. '<input type="hidden" name="comment" value="" class="cash_input cash_input_comment" />'
			. '<input type="submit" value="sign me up" class="button" /><br />'
			. '</form>'
			. '<div class="cash_notation">'
			. $this->options->message_privacy
			. '</div>';
		if ($this->status_uid == 'people_signup_200' || $this->status_uid == 'people_verifyaddress_200') {
			$this->status_uid = 'final';
		}
		switch ($this->status_uid) {
			case 'final':
				// successful submit, return messaging and optionally an asset link
				$markup = '<div class="cash_success '. self::type .'">';
				$show_final_message = true;
				if ($this->status_uid == 'people_signup_200' && !$this->options->do_not_verify) {
					// if this is a first submit and we're verifying the email, first check to see if it's been verified already
					$verification_request = new CASHRequest(array(
						'cash_request_type' => 'people', 
						'cash_action' => 'checkverification',
						'address' => $this->original_request->response['payload']['address'],
						'list_id' => $this->options->email_list_id
					));
					if (!$verification_request->response['payload']) {
						// not verified, so do not show the final message, and instead give a "you must verify" jam
						$show_final_message = false;
						$markup .= 'You must verify your email address to continue. An email has been sent. Click the link provided and you will be brought back here.<br /><br />(If you do not see the message, check your SPAM folder.)';
					}
				} 
				if ($show_final_message) {
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