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

use Cashmusic\Elements\subscription as SubscriptionElement;

class Subscription extends ElementBase {
	public $type = 'subscription';
	public $name = 'Subscription';

	public function getData() {

        $this->startSession();

        //$session = new CASHRequest(null);
        $subscriber_id = ($this->sessionGet("user_id")) ? $this->sessionGet("user_id") : false;
        $plan_user_id =  $this->element_data['user_id'];

        $this->element_data['subscriber_id'] = $subscriber_id;

        $plan_id = $this->sessionGet("plan_id");
        $authenticated = $this->sessionGet("subscription_authenticated");

		// this is where we get data
		$subscription_element = new SubscriptionElement\Data(
            $plan_user_id,
            $this->element_data['plan_id']
			);

		$this->updateElementData($subscription_element->data);

        if (!$this->element_data['paypal_connection'] && !$this->element_data['stripe_public_key']) {
            $this->setError("No valid payment connection found.");
        }

        // if we're logged in already, show them the my account button instead of login
        if ($plan_id == $this->element_data['plan_id'] && $authenticated) {
            $this->element_data['logged_in'] = true;
        }

        // authentication process start
		if (!empty($_REQUEST['key'])) {
            $validate_request = new CASHRequest(
                array(
                    'cash_request_type' => 'system',
                    'cash_action' => 'validateresetflag',
                    'address' => $_REQUEST['address'],
                    'key' => $_REQUEST['key']
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
		}

		if (isset($_REQUEST['state'])) {
            // set state and fire the appropriate method in Element\State class

            $this->element_data['email_address'] = $this->sessionGet('email_address');
            $this->element_data['user_id'] = $this->sessionGet('user_id');

            $subscription_state = new SubscriptionElement\States($subscriber_id);

            $subscription_state->router(function($template, $values) {
                $this->setTemplate($template);
                $this->updateElementData($values);
            });
		}


		return $this->element_data;
	}
} // END class
?>
