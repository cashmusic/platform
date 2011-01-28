<?php
/**
 * Email For Download element
 *
 * @package seed.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class EmailForDownload {
	protected $name = 'Email For Download';
	protected $type = 'emailfordownload';
	protected $status_uid;
	protected $options;
	
	public function __construct($status_uid=false,$options=false) {
		$this->status_uid = $status_uid;
		$this->options = $options;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getMarkup() {
		$markup = '';
		switch ($this->status_uid) {
			case 'emaillist_signup_200':
				$unlock_request = new SeedRequest(array(
					'seed_request_type' => 'asset', 
					'seed_action' => 'unlock',
					'asset_id' => $this->options->asset_id
				));
				$asset_request = new SeedRequest(array(
					'seed_request_type' => 'asset', 
					'seed_action' => 'getasset',
					'asset_id' => $this->options->asset_id
				));
				$asset_title = $asset_request->response['payload']['title'];
				$asset_description = $asset_request->response['payload']['description'];
				$markup = "<div class=\"seed_success ". $this->type ."\">";
				$markup .= $this->options->message_success . "<br /><br />";
				$markup .= "<a href=\"?seed_request_type=asset&seed_action=claim&asset_id=".$this->options->asset_id."\" class=\"download\">$asset_title</a>";
				$markup .= "<div class=\"description\">$asset_description</div>";
				$markup .= "</div>";
				break;
			case 'emaillist_signup_400':
				$markup = "<div class=\"seed_error ". $this->type ."\">";
				$markup .= $this->options->message_invalid_email;
				$markup .= "</div>";
				$markup .= "<form class=\"seed_form ". $this->type ."\" method=\"post\" action=\"\">";
				$markup .= "<input type=\"text\" name=\"address\" value=\"\" class=\"seed_input\" />";
				$markup .= "<input type=\"hidden\" name=\"seed_request_type\" value=\"emaillist\" />";
				$markup .= "<input type=\"hidden\" name=\"seed_action\" value=\"signup\" />"; 
				$markup .= "<input type=\"hidden\" name=\"list_id\" value=\"".$this->options->emal_list_id."\" />";
				$markup .= "<input type=\"hidden\" name=\"verified\" value=\"1\" />";
				$markup .= "<input type=\"submit\" value=\"sign me up\" class=\"button\" /><br />";
				$markup .= "</form>";
				$markup .= "<div class=\"seed_notation\">";
				$markup .= $this->options->message_privacy;
				$markup .= "</div>";
				break;
			default:
				$markup = "<form class=\"seed_form ". $this->type ."\" method=\"post\" action=\"\">";
				$markup .= "<input type=\"text\" name=\"address\" value=\"\" class=\"seed_input\" />";
				$markup .= "<input type=\"hidden\" name=\"seed_request_type\" value=\"emaillist\" />";
				$markup .= "<input type=\"hidden\" name=\"seed_action\" value=\"signup\" />"; 
				$markup .= "<input type=\"hidden\" name=\"list_id\" value=\"".$this->options->emal_list_id."\" />";
				$markup .= "<input type=\"hidden\" name=\"verified\" value=\"1\" />";
				$markup .= "<input type=\"submit\" value=\"sign me up\" class=\"button\" /><br />";
				$markup .= "</form>";
				$markup .= "<div class=\"seed_notation\">";
				$markup .= $this->options->message_privacy;
				$markup .= "</div>";
		}
		return $markup;	
	}
} // END class 
?>