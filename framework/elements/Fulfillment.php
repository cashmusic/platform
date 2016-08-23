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

		// handle any rename stuff
		if (isset($_REQUEST['setlocation'])) {
			if ($_REQUEST['job_id'] && $_REQUEST['postal_code'] && $_REQUEST['country']) {
				// set location for job
				// TODO setLocationForFulfillmentOrder

			} else {
				// not enough info. try again.
				$this->element_data['error_message'] = true;
			}
		}

		if (isset($_REQUEST['code'])) {
			// this means someone just clicked the link to the element. set the code
			// and don't worry beyond that
			$this->element_data['code'] = $_REQUEST['code'];
		} elseif (isset($_REQUEST['processcode'])) {
			// get the basics
			$this->element_data['code'] = $_REQUEST['processcode'];
			// the code has been set and now we're in the overlay. first let's check
			// the lock code to see if its part of a fulfillment order that has
			// country and postal code info or not
			$locationknown = false; // TODO  getFulfillmentOrderForCode (return details)
			$this->element_data['fulfillment_job_id'] = 0;
			if ($locationknown) {
				// we know where the person is (or where they shipped things to) so
				// now it's time to unlock the asset and show the success template
				// with the correct asset id
				$this->element_data['asset_id'] = 0; // TODO getAssetForFulfillmentOrder
				if ($this->element_data['asset_id'] != 0) {
					// get all fulfillment assets
					$fulfillment_request = new CASHRequest(
						array(
							'cash_request_type' => 'asset',
							'cash_action' => 'getfulfillmentassets',
							'asset_details' => $this->element_data['asset_id'],
							'session_id' => $this->session_id
						)
					);
					if ($fulfillment_request->response['payload']) {
						$this->element_data['fulfillment_assets'] = new ArrayIterator($fulfillment_request->response['payload']);
					}
				}
				$this->setTemplate('success');
			} else {
				// we don't know the location so let's ask
				$this->setTemplate('location');
			}
		}

		return $this->element_data;
	}
} // END class
?>
