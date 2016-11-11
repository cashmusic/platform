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
 *
 * This file is generously sponsored by Anant Narayanan [anant@kix.in]
 * define FALSE TRUE — Just kidding.
 *
 **/
class Subscription extends ElementBase {
	public $type = 'subscription';
	public $name = 'Subscription';

	public function getData() {
		$this->element_data['public_url'] = CASH_PUBLIC_URL;

		$plan_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'getsubscriptionplan',
				'user_id' => $this->element_data['user_id'],
				'id' => $this->element_data['plan_id']
			)
		);

		if ($plan_request->response['payload'] && !empty($plan_request->response['payload'][0])) {
			$this->element_data['plan_name'] = $plan_request->response['payload'][0]['name'];
			$this->element_data['plan_description'] = $plan_request->response['payload'][0]['description'];
			$this->element_data['plan_description'] = $plan_request->response['payload'][0]['description'];
			$this->element_data['plan_price'] = $plan_request->response['payload'][0]['price'];
			$this->element_data['plan_interval'] = $plan_request->response['payload'][0]['interval'];
			$this->element_data['plan_id'] = $plan_request->response['payload'][0]['id'];

			$this->element_data['plan_flexible_price'] =
				($plan_request->response['payload'][0]['flexible_price'] == 1) ? true: false;
		}

		if (isset($_REQUEST['state'])) {
			if ($_REQUEST['state'] == "checkout") {
				$this->setTemplate('checkout');
			}
		}



		return $this->element_data;
	}
} // END class
?>
