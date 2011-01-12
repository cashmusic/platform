<?php
/**
 * Email For Download element
 *
 * @package seed.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class EmailForDownload {
	public function __construct($status_uid,$options) {
		$this->status_uid = $status_uid;
		$this->options = $options;
	}
	
	public function getMarkup() {
		$markup = '';
		switch ($this->status_uid) {
			case 'emaillist_signup_200':
				$markup = "Thanks! You're all signed up. Here's your download: <br /><br />";
				$markup .= "<a href=\"?down=xx\" class=\"download\">“You Wouldn’t Have To Ask” MP3</a>";
				break;
			case 'emaillist_signup_400':
				$markup = "<div class=\"error\">";
				$markup .= "Sorry, that email address wasn't valid. Please try again.";
				$markup .= "</div>";
				$markup .= "<form method=\"post\" action=\"\">";
				$markup .= "<input type=\"text\" name=\"address\" value=\"\" style=\"width:18em;\" />";
				$markup .= "<input type=\"hidden\" name=\"seed_request_type\" value=\"emaillist\" />";
				$markup .= "<input type=\"hidden\" name=\"seed_action\" value=\"signup\" />"; 
				$markup .= "<input type=\"hidden\" name=\"list_id\" value=\"1\" />";
				$markup .= "<input type=\"hidden\" name=\"verified\" value=\"1\" />";
				$markup .= "<input type=\"submit\" value=\"sign me up\" class=\"button\" /><br />";
				$markup .= "</form>";
				$markup .= "<div class=\"notation\">";
				$markup .= "We won't share, sell, or be jerks with your email address.";
				$markup .= "</div>";
				break;
			default:
				$markup = "<form method=\"post\" action=\"\">";
				$markup .= "<input type=\"text\" name=\"address\" value=\"\" style=\"width:18em;\" />";
				$markup .= "<input type=\"hidden\" name=\"seed_request_type\" value=\"emaillist\" />";
				$markup .= "<input type=\"hidden\" name=\"seed_action\" value=\"signup\" />"; 
				$markup .= "<input type=\"hidden\" name=\"list_id\" value=\"1\" />";
				$markup .= "<input type=\"hidden\" name=\"verified\" value=\"1\" />";
				$markup .= "<input type=\"submit\" value=\"sign me up\" class=\"button\" /><br />";
				$markup .= "</form>";
				$markup .= "<div class=\"notation\">";
				$markup .= "We won't share, sell, or be jerks with your email address.";
				$markup .= "</div>";
		}
		return $markup;	
	}
} // END class 
?>