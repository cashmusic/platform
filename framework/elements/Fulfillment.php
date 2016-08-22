<?php
/**
 * Email For Download element
 *
 * @package downloadcodes.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class Fulfillment extends ElementBase {
	public $type = 'fulfillment';
	public $name = '3rd Party Fulfillment';

	public function getData() {
		$this->element_data['element_id'] = $this->element_id;
		$this->element_data['public_url'] = CASH_PUBLIC_URL;

		if ($this->status_uid == 'asset_redeemcode_400') {
			$this->element_data['error_message'] = 'That code is not valid or has already been used.';
		} elseif (isset($_REQUEST['code'])) {
			$this->element_data['code'] = $_REQUEST['code'];
			//$this->setTemplate('success');
		} elseif (isset($_REQUEST['processcode'])) {
			$this->element_data['code'] = $_REQUEST['processcode'];
			$this->setTemplate('location');
		}

		return $this->element_data;
	}
} // END class
?>
