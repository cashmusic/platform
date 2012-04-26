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
class DigitalPurchase extends ElementBase {
	const type = 'digitalpurchase';
	const name = 'Digital Purchase';

	public function getMarkup() {
		// define $markup to store all screen output
		$markup = '';
		$item_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'getitem',
				'id' => $this->options->item_id
			)
		);
		$item = $item_request->response['payload'];
		// the default form and basic elements:
		$default_markup = '<span class="cash_digitalpurchase_itemname">' . $item['name'] . '</span> '
			. '<span class="cash_digitalpurchase_itemprice">$' . $item['price'] . '</span> '
			. '<span class="cash_digitalpurchase_itemdescription">' . $item['description'] . '</span> '
			. '<form id="cash_'. self::type .'_form_' . $this->element_id . '" class="cash_form '. self::type .'" method="post" action="">'
			. '<input type="hidden" name="cash_request_type" value="commerce" />'
			. '<input type="hidden" name="cash_action" value="initiatecheckout" />'
			. '<input type="hidden" name="element_id" value="' . $this->element_id . '" class="cash_input cash_input_element_id" />'
			. '<input type="hidden" name="item_id" value="' . $this->options->item_id . '" />'
			. '<input type="hidden" name="connection_id" value="' . $this->options->connection_id . '" />'
			. '<input type="hidden" name="user_id" value="' . $this->element['user_id'] . '" />'
			. '<input type="submit" value="$' . $item['price'] . ' - Buy it now" class="button" /><br />'
			. '</form>';
		if ($this->status_uid == 'commerce_finalizepayment_200' || $this->status_uid == 'element_redeemcode_200') {
			$this->status_uid = 'final';
		}
		switch ($this->status_uid) {
			case 'final':
				if ($item['fulfillment_asset'] != 0) {
					// first we "unlock" the asset, telling the platform it's okay to generate a link for non-private assets
					$unlock_request = new CASHRequest(array(
						'cash_request_type' => 'asset', 
						'cash_action' => 'unlock',
						'id' => $item['fulfillment_asset']
					));
					// next we make the link
					$asset_request = new CASHRequest(array(
						'cash_request_type' => 'asset', 
						'cash_action' => 'getasset',
						'id' => $item['fulfillment_asset']
					));
					$asset_title = $asset_request->response['payload']['title'];
					$asset_description = $asset_request->response['payload']['description'];
					$markup = '<span class="cash_digitalpurchase_itemname">' . $item['name'] . '</span> '
							. '<span class="cash_digitalpurchase_itemdescription">' . $this->options->message_success . '</span> '
							. '<br /><br />'
							. '<a href="?cash_request_type=asset&cash_action=claim&id='.$item['fulfillment_asset'].'&element_id='.$this->element_id.'" class="download">'. $asset_title .'</a>'
							. '<div class="description">' . $asset_description . '</div>';
				}
				break;
			case 'commerce_finalizepayment_400':
				// payerid is specific to paypal, so this is temporary to tell between canceled and errored:
				if (isset($_GET['PayerID'])) {
					$markup = '<div class="cash_error '. self::type .'">'
					. $this->options->message_error
					. '</div>'
					. $default_markup;
				} else {
					$markup = $default_markup;
				}
				break;
				case 'element_redeemcode_400':
					$markup = '<div class="cash_error '. self::type .'">'
					. $this->options->message_error
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