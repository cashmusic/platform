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

		// payment connection settings
		$this->element_data['paypal_connection'] = false;
		$this->element_data['stripe_public_key'] = false;
        $this->element_data['verification'] = false;

        $this->startSession();

		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'getsettings',
				'type' => 'payment_defaults',
				'user_id' => $this->element_data['user_id']
			)
		);
		if (is_array($settings_request->response['payload'])) {

			if (isset($settings_request->response['payload']['stripe_default'])) {
				if ($settings_request->response['payload']['stripe_default']) {
					$payment_seed = new StripeSeed($this->element_data['user_id'],$settings_request->response['payload']['stripe_default']);
					if (!empty($payment_seed->publishable_key)) {
						$this->element_data['stripe_public_key'] = $payment_seed->publishable_key;
					}
				}
			}
		} else {
			if (isset($this->element_data['connection_id'])) {
				$connection_settings = CASHSystem::getConnectionTypeSettings($this->element_data['connection_type']);
				$seed_class = $connection_settings['seed'];
				if ($seed_class == 'StripeSeed') {
					$payment_seed = new StripeSeed($this->element_data['user_id'],$this->element_data['connection_id']);
					if (!empty($payment_seed->publishable_key)) {
						$this->element_data['stripe_public_key'] = $payment_seed->publishable_key;
					}
				}
			}
		}


		if (!$this->element_data['paypal_connection'] && !$this->element_data['stripe_public_key']) {
			$this->setError("No valid payment connection found.");
		}

		// get plan data
		$plan_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'getsubscriptionplan',
				'user_id' => $this->element_data['user_id'],
				'id' => $this->element_data['plan_id']
			)
		);

		$currency_request = new CASHRequest(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'getsettings',
				'type' => 'use_currency',
				'user_id' => $this->element_data['user_id']
			)
		);
		if ($currency_request->response['payload']) {
			$this->element_data['currency'] = CASHSystem::getCurrencySymbol($currency_request->response['payload']);
		} else {
			$this->element_data['currency'] = CASHSystem::getCurrencySymbol('USD');
		}

		if ($plan_request->response['payload'] && !empty($plan_request->response['payload'][0])) {
			$this->element_data['plan_name'] = $plan_request->response['payload'][0]['name'];

			$this->element_data['plan_description'] = $plan_request->response['payload'][0]['description'];
			$this->element_data['flexible_price'] = $plan_request->response['payload'][0]['flexible_price'];

			$this->element_data['plan_price'] = $plan_request->response['payload'][0]['price'];

			// if flexible pricing is set let's set the default to suggested price
			if (!empty($this->element_data['flexible_price'])) {
				$this->element_data['plan_price'] = $plan_request->response['payload'][0]['suggested_price'];
				$this->element_data['minimum_price'] = $plan_request->response['payload'][0]['price'];
			} else {
				$this->element_data['minimum_price'] = $this->element_data['plan_price'];
			}

			$this->element_data['plan_interval'] = $plan_request->response['payload'][0]['interval'];

			$this->element_data['plan_id'] = $plan_request->response['payload'][0]['sku'];

			$this->element_data['plan_flexible_price'] =
				($plan_request->response['payload'][0]['flexible_price'] == 1) ? true: false;

			$this->element_data['shipping'] = ($plan_request->response['payload'][0]['physical'] == 0) ? "false": "true";

		} else {
			//error
		}

		if (!empty($_REQUEST['key'])) {


            $validate_request = new CASHRequest(
                array(
                    'cash_request_type' => 'system',
                    'cash_action' => 'validateresetflag',
                    'address' => $_GET['address'],
                    'key' => $_GET['key']
                )
            );

            if ($validate_request->response['payload']) {
                $this->element_data['key'] = true;
				$email = isset($_REQUEST['address']) ? $_REQUEST['address'] : "";

				if (empty($email)) {
                    $this->element_data['error_message'] = "Something went wrong.";

                    return $this->element_data;
				}

                $this->sessionSet('email_address', $email);

                $user_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'people',
                        'cash_action' => 'getuseridforaddress',
                        'address' => $email
                    )
                );
                if ($user_request->response['payload']) {
                    $this->sessionSet('user_id', $user_request->response['payload']);
				} else {
                    $this->element_data['error_message'] = "We couldn't find your user.";
                    return $this->element_data;
				}
			} else {
                $this->element_data['error_message'] = "Something went wrong.";
			}


			//https://s3-us-west-2.amazonaws.com/cashmusic.tests.for.tom/element.html?key=e48fcb1a48d1e0e77bed52addb842f13&address=tom%40tos.com&element_id=3

			//  <script src="https://192.168.33.99/public/cashmusic.js" data-element="3"></script>

			// make sure password reset exists

			// show password

			// or error
		}

		if (isset($_REQUEST['state'])) {

            $this->element_data['email_address'] = $this->sessionGet('email_address');
            $this->element_data['user_id'] = $this->sessionGet('user_id');

			if ($_REQUEST['state'] == "verified") {
				$this->setTemplate('settings');
			}

			if ($_REQUEST['state'] == "validatelogin") {

				// check if the passwords actually match
				if($_REQUEST['password'] != $_REQUEST['confirm_password']) {
					$this->element_data['error_message'] = "Your password confirmation doesn't match.";
                    $this->setTemplate('settings');
				}

                if (!defined('MINIMUM_PASSWORD_LENGTH')) {
                    define('MINIMUM_PASSWORD_LENGTH',10);
                }
                if (strlen($_REQUEST['password']) < MINIMUM_PASSWORD_LENGTH) {
                    $this->element_data['error_message'] = "Minimum password lengh of 10 characters.";
                    $this->setTemplate('settings');
                }


				// validate the request to change things

                $password_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'system',
                        'cash_action' => 'setlogincredentials',
                        'user_id' => $this->element_data['user_id'],
						'password' => $_REQUEST['password']
                    )
                );

                if ($password_request->response['payload'] !== false) {
					error_log("fuck yeah");
                } else {
                    $this->element_data['error_message'] = "There was an error setting your password.";
                    $this->setTemplate('settings');
				}

/*                $validate_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'system',
                        'cash_action' => 'validatelogin',
                        'address' => $this->element_data['email_address'],
                        'password' => $_REQUEST['password']
                    )
                );*/

				error_log(
					json_encode($validate_request)
				);
			}
		}



		return $this->element_data;
	}
} // END class
?>
