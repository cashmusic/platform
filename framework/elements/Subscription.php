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

        // to avoid confusion down the line, set an element user ID and a subscriber user ID now
        $plan_user_id =  $this->element_data['user_id'];
        $subscriber_id = $this->element_data['subscriber_id'] = ($this->sessionGet("user_id")) ? $this->sessionGet("user_id") : false;

        $this->element_data['email_address'] = ($this->sessionGet("email_address")) ? $this->sessionGet("email_address") : false;

        $plan_id = (!empty($this->sessionGet("plan_id"))) ? $this->sessionGet("plan_id") : false;
        $element_id = $this->element_data['element_id'];
        $authenticated = false;

        if (!empty($this->sessionGet("subscription_authenticated")) || !empty($subscriber_id)) {
            $authenticated = true;
            $this->sessionSet("subscription_authenticated", true);
        }

        // check for logged in state
        $this->element_data['logged_in'] = false;

        if ($this->sessionGet('logged_in')) {
            $this->element_data['logged_in'] = true;
        }


        $plans = [];

        $subscription_data = new SubscriptionElement\Data($plan_user_id);

        foreach ($this->element_data['plans'] as $plan) {
            $plans[] = $subscription_data->getPlan($plan['plan_id']);
        }

        // this is where we get data
        $this->updateElementData(['all_plans'=>$plans]);

        // get
        $this->updateElementData($subscription_data->getConnections());
        $this->updateElementData($subscription_data->getCurrency());

        if (!$this->element_data['paypal_connection'] && !$this->element_data['stripe_public_key']) {
            $this->setError("No valid payment connection found.");
        }

        // if we're logged in already, show them the my account button instead of login
        if (in_array($plan_id, $this->element_data['plans']) && $authenticated) {
            $this->element_data['logged_in'] = true;
        }

        //TODO: this is also a problem if someone wants one plan to not be flexible price
        $this->element_data['flexible_price'] = false;

        foreach($this->element_data['all_plans'] as $plan) {
            if ($plan['flexible_price'] == 1) $this->element_data['flexible_price'] = true;
        }

        // check if $_REQUEST['key'] is set and do thingss
        $this->updateElementData(
            $this->processVerificationKey()
        );

        if (!empty($_REQUEST['state'])) {

            // set state and fire the appropriate method in Element\State class
            $subscription_state = new SubscriptionElement\States($this->element_data, $element_id, $subscriber_id, $plan_user_id, $plan_id, $this->session_id);

            $subscription_state->router(function ($template, $values) {
                $this->setTemplate($template);
                $this->updateElementData($values);
            });


        }

		return $this->element_data;
	}

	private function processVerificationKey() {

        $data = [];

        if (!empty($_REQUEST['key'])) {
            $validate_request = new \CASHRequest(
                array(
                    'cash_request_type' => 'system',
                    'cash_action' => 'validateresetflag',
                    'address' => $_REQUEST['address'],
                    'key' => $_REQUEST['key']
                )
            );

            if ($validate_request->response['payload']) {


                $data['key'] = true;
                $email = isset($_REQUEST['address']) ? $_REQUEST['address'] : "";

                if (empty($email)) {
                    $data['error_message'] = "Something went wrong.";

                    return $data;
                }

                $user_request = new \CASHRequest(
                    array(
                        'cash_request_type' => 'people',
                        'cash_action' => 'getuseridforaddress',
                        'address' => $email
                    )
                );

                $data['email_address'] = $email;
                $this->sessionSet("email_address", $email);

                if ($user_request->response['payload']) {
                    $data['user_id'] = $user_request->response['payload'];
                    $data['subscriber_id'] = $data['user_id'];
                    $this->sessionSet("subscription_id", $user_request->response['payload']);

                    //$subscriber_id = $user_request->response['payload'];
                } else {
                    $data['error_message'] = "We couldn't find your user.";
                }
            } else {
                $data['error_message'] = "Something went wrong.";
            }

            return $data;
        }
    }
} // END class
?>