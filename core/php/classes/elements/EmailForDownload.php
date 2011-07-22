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
class EmailForDownload {
	const type = 'emailfordownload';
	const name = 'Email For Download';
	protected $status_uid;
	protected $options;
	
	public function __construct($status_uid=false,$options=false) {
		$this->status_uid = $status_uid;
		$this->options = $options;
	}
	
	public function getName() {
		return self::name;
	}
	
	public function getType() {
		return self::type;
	}
	
	public function getMarkup() {
		$markup = '';
		switch ($this->status_uid) {
			case 'userlist_signup_200':
				$unlock_request = new CASHRequest(array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'unlock',
					'asset_id' => $this->options->asset_id
				));
				$asset_request = new CASHRequest(array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'getasset',
					'asset_id' => $this->options->asset_id
				));
				$asset_title = $asset_request->response['payload']['title'];
				$asset_description = $asset_request->response['payload']['description'];
				$markup = "<div class=\"seed_success ". self::type ."\">"
				. $this->options->message_success . "<br /><br />"
				. "<a href=\"?cash_request_type=asset&cash_action=claim&asset_id=".$this->options->asset_id."\" class=\"download\">$asset_title</a>"
				. "<div class=\"description\">$asset_description</div>"
				. "</div>";
				break;
			case 'userlist_signup_400':
				$markup = "<div class=\"seed_error ". self::type ."\">"
				. $this->options->message_invalid_email
				. "</div>"
				. "<form class=\"seed_form ". self::type ."\" method=\"post\" action=\"\">"
				. "<input type=\"email\" name=\"address\" value=\"\" class=\"seed_input\" />"
				. "<input type=\"hidden\" name=\"cash_request_type\" value=\"userlist\" />"
				. "<input type=\"hidden\" name=\"cash_action\" value=\"signup\" />"
				. "<input type=\"hidden\" name=\"list_id\" value=\"".$this->options->emal_list_id."\" />"
				. "<input type=\"hidden\" name=\"verified\" value=\"1\" />"
				. "<input type=\"submit\" value=\"sign me up\" class=\"button\" /><br />"
				. "</form>"
				. "<div class=\"seed_notation\">"
				. $this->options->message_privacy
				. "</div>";
				break;
			default:
				$markup = "<form class=\"seed_form ". self::type ."\" method=\"post\" action=\"\">"
				. "<input type=\"email\" name=\"address\" value=\"\" class=\"seed_input\" />"
				. "<input type=\"hidden\" name=\"cash_request_type\" value=\"userlist\" />"
				. "<input type=\"hidden\" name=\"cash_action\" value=\"signup\" />"
				. "<input type=\"hidden\" name=\"list_id\" value=\"".$this->options->emal_list_id."\" />"
				. "<input type=\"hidden\" name=\"verified\" value=\"1\" />"
				. "<input type=\"submit\" value=\"sign me up\" class=\"button\" /><br />"
				. "</form>"
				. "<div class=\"seed_notation\">"
				. $this->options->message_privacy
				. "</div>";
		}
		return $markup;	
	}
} // END class 
?>