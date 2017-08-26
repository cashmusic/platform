<?php

namespace CASHMusic\Elements\Subscription;

use CASHMusic\Core\CASHSystem;
use CASHMusic\Core\ElementBase;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Elements\Subscription\Extensions\Misc;
use CASHMusic\Elements\Subscription\Extensions\Router;
use CASHMusic\Elements\Subscription\Extensions\States;

class Subscription extends ElementBase {

    use Router;
    use States;
    use Misc;

    protected $state, $plan_id, $email_address, $element_user_id, $subscriber_id;
	public $type = 'subscription';
	public $name = 'Subscription';

	public function getData() {

        $this->state = !empty($_REQUEST['state']) ? $_REQUEST['state'] : "default";

        $this->element_data['subscription_id'] = $this->sessionGet("subscription_id");
        $this->element_data['email_address'] = $this->sessionGet("email_address");

        if (!$plan_id = $this->sessionGet("plan_id")) {
            $plan_id = false;
        }

        $this->element_data['logged_in'] = false;

        $authenticated = false;

        // get plan data based on plan ids. works for multiples
        $plans = [];

        $subscription_data = new ElementData($this->element_data['user_id']);

        foreach ($this->element_data['plans'] as $plan) {
            $plans[] = $subscription_data->getPlan($plan['plan_id']);
        }

        // add plan data to element_data array
        $this->updateElementData(['all_plans'=>$plans]);

        // get connections and currency
        $this->updateElementData($subscription_data->getConnections());
        $this->updateElementData($subscription_data->getCurrency());

        if (!$this->element_data['paypal_connection'] && !$this->element_data['stripe_public_key']) {
            //return false; // no valid payment found error
        }

        if (!empty($this->element_data['subscription_id'])) {
            $authenticated = true;
        }
        // if we're logged in already, maybe show them a logout button
        if (in_array($plan_id, $this->element_data['plans']) && $authenticated || $this->sessionGet('logged_in')) {
            $this->element_data['logged_in'] = true;
        }

        //TODO: this is also a problem if someone wants one plan to not be flexible price
        $this->element_data['flexible_price'] = false;

        foreach($this->element_data['all_plans'] as $plan) {
            if ($plan['flexible_price'] == 1) $this->element_data['flexible_price'] = true;
        }

        // check if $_REQUEST['key'] is set and do verify-y things
        $verification_data = $this->processVerificationKey();

        $this->updateElementData(
            $verification_data
        );

        // form submission handling.
        $this->checkRequestForFormSubmission();

        if (!empty($this->element_data['subscription_id'])) {
            $this->user_id = $this->element_data['subscription_id'];
        } else {
            if ($session_user_id = $this->sessionGet("subscription_id")) $this->user_id = $session_user_id;
        }

        $this->plan_id = $plan_id;
        $this->email_address = $this->element_data['email_address'];
        $this->element_user_id = $this->element_data['user_id'];
        
        // set state and fire the appropriate method in Element\State class
        $this->router(function ($template, $values) {
            $this->setTemplate($template);
            $this->updateElementData($values);
        });

		return $this->element_data;
	}
} // END class
?>