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
			. '<input type="hidden" name="list_id" value="'.$this->options->emal_list_id.'" class="cash_input cash_input_list_id" />'
			. '<input type="hidden" name="verified" value="1" class="cash_input cash_input_verified" />'
			. '<input type="hidden" name="comment" value="" class="cash_input cash_input_comment" />'
			. '<input type="submit" value="sign me up" class="button" /><br />'
			. '</form>'
			. '<div class="cash_notation">'
			. $this->options->message_privacy
			. '</div>';
		switch ($this->status_uid) {
			case 'people_signup_200':
				// successful submit, return asset link
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
				$markup = '<div class="cash_success '. self::type .'">' . $this->options->message_success;
				if ($this->options->asset_id !== 0) {
					$markup .= '<br /><br />'
					. '<a href="?cash_request_type=asset&cash_action=claim&id='.$this->options->asset_id.'&element_id='.$this->element_id.'" class="download">'. $asset_title .'</a>'
					. '<div class="description">' . $asset_description . '</div>';
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