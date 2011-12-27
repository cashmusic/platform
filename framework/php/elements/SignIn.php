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
class SignIn extends ElementBase {
	const type = 'signin';
	const name = 'Sign-In';
	
	protected function init() {
		if ($this->status_uid == 'people_signintolist_200' && !$this->unlocked) {
			// unlock the element
			$this->unlock();
		}
		if ($this->sessionGet('initialized_element_' . $this->element_id,'script')) {
			// element is initialized, meaning this is the closing embed
			// unset element initialized state:
			$this->sessionClear('initialized_element_' . $this->element_id,'script');
			if ($this->unlocked) {
				// unlocked, so clean out the buffer and don't display anything further
				$this->status_uid = 'empty';
				if (ob_get_level()) {
					ob_end_flush();
				}
			} else {
				// locked, delete the protected output and send an empty string
				$this->status_uid = 'empty';
				if (ob_get_level()) {
					ob_end_clean();
				}
			}
		} else {
			if ($this->unlocked) {
				// element already unlocked. do nothing.
				$this->status_uid = 'empty';
			} else {
				// element is locked. mark element as initialized, start output buffering, and display default markup
				$this->sessionSet('initialized_element_' . $this->element_id,true,'script');
				ob_start();
			}
		}
	}

	public function getMarkup() {
		// define $markup to store all screen output
		$markup = '';
		// the default form and basic elements:
		$default_markup = '<form id="cash_'. self::type .'_form_' . $this->element_id . '" class="cash_form '. self::type .'" method="post" action="">';
		if ($this->options->display_title) {
			$default_markup .= '<h2 class="cash_title">' . $this->options->display_title . '</h2>';
		}
		if ($this->options->display_message) {
			$default_markup .= '<p class="cash_message">' . $this->options->display_message . '</p>';
		}
		$default_markup .= '<div class="cash_address_container"><label for="address">Email</label>'
			. '<input type="email" name="address"  placeholder="Your Email Address" value="" class="cash_input cash_input_address" /></div>'
			. '<div class="cash_password_container"><label for="password">Password</label>'
			. '<input type="password" name="password" value="" class="cash_input cash_input_password" /></div>'
			. '<div class="cash_hidden"><input type="hidden" name="cash_request_type" value="people" />'
			. '<input type="hidden" name="cash_action" value="signintolist" />'
			. '<input type="hidden" name="list_id" value="'.$this->options->emal_list_id.'" class="cash_input cash_input_list_id" />'
			. '<input type="hidden" name="element_id" value="'.$this->element_id.'" class="cash_input cash_input_element_id" /></div>'
			. '<input type="submit" value="log in" class="button" /><br />'
			. '</form>';
		switch ($this->status_uid) {
			case 'people_signintolist_400':
				// error, likely in the email format. error message + default form
				$markup = '<div class="cash_error '. self::type .'">'
				. $this->options->message_invalid_email
				. '</div>'
				. $default_markup;
				break;
			case 'empty':
				$markup = '';
				break;
			default:
				// default form
				$markup = $default_markup;
		}
		return $markup;
	}
} // END class 
?>