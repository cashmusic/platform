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
	const type = 'tourdates';
	const name = 'Tour Dates';

	public function getMarkup() {
		$markup = '';
		$markup = '<form id="cash_'. self::type .'_form_' . $this->element_id . '" class="cash_form '. self::type .'" method="post" action="">'
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
		return $markup;	
	}
} // END class 
?>