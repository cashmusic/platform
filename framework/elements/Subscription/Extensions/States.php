<?php

namespace CASHMusic\Elements\Subscription\Extensions;

use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Plants\Commerce\CommercePlant;
use CASHMusic\Elements\Subscription\ElementData;


trait States
{
    /**
     * Various state methods, to keep the switch/case more legible
     */

    private function stateLogin() {
        return [
            'template' => 'login',
            'data' => []
        ];
    }

    private function stateSuccess() {

        $this->revokeLoginState();

        return [
            'template' => 'success',
            'data' => []
        ];
    }

    private function stateVerified() {

        $data = [];
        $subscriber_id = $this->sessionGet('subscription_id');
        $data['email'] = $this->sessionGet("email_address");
        $user_request = new CASHRequest(
            array(
                'cash_request_type' => 'people',
                'cash_action' => 'getuser',
                'user_id' => $subscriber_id
            )
        );

        if ($user_request->response['payload']) {

            $user_data = $user_request->response['payload']['data'];

            if (empty($user_data['new_subscriber'])) {
                $data['has_password'] = true;

                $this->setLoginState();
            }
        }

        return [
            'template' => 'settings',
            'data' => $data
        ];
    }

    private function stateSetCredentials() {

        $data = [];

        // check if the passwords actually match
        if($_REQUEST['password'] != $_REQUEST['confirm_password']) {
            $data['error_message'] = "Your password confirmation doesn't match.";
            $template = 'settings';
        }

        if (!defined('MINIMUM_PASSWORD_LENGTH')) {
            define('MINIMUM_PASSWORD_LENGTH',10);
        }
        if (strlen($_REQUEST['password']) < MINIMUM_PASSWORD_LENGTH) {
            $data['error_message'] = "Minimum password lengh of 10 characters.";
            $template = 'settings';
        }
        // validate the request to change things
        $password_request = new CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'setlogincredentials',
                'user_id' => $this->user_id,
                'password' => $_REQUEST['password']
            )
        );

        if ($password_request->response['payload'] !== false) {

            $this->setLoginState();
            $data['items'] = $this->stateLoggedInIndex(true);

            $template = 'logged_in_index';

            $data['firstuse'] = true;
        } else {
            $data['error_message'] = "There was an error setting your password.";
            $template = 'settings';
        }

        return [
            'template' => $template,
            'data' => $data
        ];
    }

    private function stateValidateLogin() {

        $data = [];
        $template = "login";
        // verify login
        $email = (isset($_REQUEST['email'])) ? trim($_REQUEST['email']) : false;
        $password = (isset($_REQUEST['password'])) ? trim($_REQUEST['password']) : false;

        $plans = (isset($_REQUEST['plans'])) ? $_REQUEST['plans'] : false;

        $password_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'loginsubscriber',
                'email' => $email,
                'password' => $password,
                'plans' => $plans
            )
        );

        if ($password_request->response['payload']) {
            // valid login + valid subscription
            if ($password_request->response['payload'] != "401") {

                // we need to make sure this is isolated by subscription---
                // maybe later we can actually have subscriptions switchable
                list($this->user_id, $this->subscription_id) = $password_request->response['payload'];

                $this->setLoginState();
                $data['items'] = $this->stateLoggedInIndex(true);

                $template = 'logged_in_index';
            }

            if ($password_request->response['payload'] == "401") {
                $data['error_message'] = "Sorry, that's not a valid subscription login.";
                $template = 'login';
            }
        }

        return [
            'template' => $template,
            'data' => $data
        ];
    }

    private function stateLoggedInIndex($pass_data=false) {

        // make sure we're actually logged in
        if (!$this->checkLoginState()) return [
            'template' => 'login',
            'data' => ['logged_in'=>false]
        ];

        $items = [];

        if (!empty($this->element_data['items'])) {

            // get feed items so we can add some stuff
            foreach($this->element_data['items'] as $item) {
                $details = ElementData::getItemDetails($item['item_id'], $this->session_id);

                $details[$item['type']] = true;
                $items[] = array_merge($details, $item);

            }

            // we need to show newest first
            $items = array_reverse($items);
        }

        if ($pass_data) {
            return $items;
        } else {
            return [
                'template' => 'logged_in_index',
                'data' => ['logged_in'=>true, 'items'=>$items]
            ];
        }

    }

    private function stateAccountSettings() {

        // make sure we're actually logged in
        if (!$this->checkLoginState()) return [
            'template' => 'login',
            'data' => ['logged_in'=>false]
        ];

        $address = $subscriber = $payment_details = $customer = false; // defaults

        $subscriber_details = $this->getSubscriberDetails();

        if (is_cash_model($subscriber_details['subscriber'])) {
            if (isset($subscriber_details['subscriber']->data['shipping_info'])) {
                $address = $subscriber_details['subscriber']->data['shipping_info'];
            }

            $subscriber = $subscriber_details['subscriber'];
            $user = var_dump($subscriber_details['user']);
        }

        if (is_cash_model($subscriber_details['payment'])) {
            $payment_details = $subscriber_details['payment'];
        }

        if (isset($subscriber_details['customer'])) {
            $customer = $subscriber_details['customer'];
        }

        return [
            'template' => 'account/main',
            'data' => compact('address', 'subscriber','payment_details', 'customer', 'user')
        ];
    }

    private function stateEditAddress() {

        $address = false;

        if (!isset($_REQUEST['action'])) {
            $subscriber_details = $this->getSubscriberDetails();

            if (is_cash_model($subscriber_details['subscriber'])) {
                if (isset($subscriber_details['subscriber']->data['shipping_info'])) {
                    $address = $subscriber_details['subscriber']->data['shipping_info'];
                }
            }

        }

        return [
            'template' => 'account/address',
            'data' => ['address'=>$address, 'logged_in'=>true, 'session_id'=>$this->session_id]
        ];
    }

    private function stateForgotPassword() {
        return [
            'template' => 'forgot_password',
            'data' => []
        ];
    }

    private function stateResetPassword() {

        $data = [];
        $message = "There was an error resetting your password";
        $template = "reset_password";
        $finalize_url = (isset($_REQUEST['finalize_url'])) ? $_REQUEST['finalize_url'] : false;
        $submitted_email_address = (isset($_REQUEST['email'])) ? $_REQUEST['email'] : false;

        if (!$finalize_url) {
            $data['error_message'] = $message;
            return [
                'template' => 'forgot_password',
                'data' => $data
            ];
        }

        if (!$submitted_email_address) {
            $data['error_message'] = "That's not a valid email, try again.";
            return [
                'template' => 'forgot_password',
                'data' => $data
            ];
        }

        $element_request = new CASHRequest(
            array(
                'cash_request_type' => 'element',
                'cash_action' => 'getelement',
                'id' => $this->element_id
            )
        );

        $email_content = $element_request->response['payload']['options']['message_reset_password_email'];

        if (!$email_content) {
            $data['error_message'] = $message;
            $template = "forgot_password";
        }

        if (!CommercePlant::sendResetValidationEmail(
            $this->element_id,
            $this->user_id,
            $submitted_email_address,
            $finalize_url,
            $email_content)) {
            $data['error_message'] = $message;
            $template = "forgot_password";
        }

        // send reset password

        // or fail

        return [
            'template' => $template,
            'data' => $data
        ];
    }

    private function stateLogout() {

        $this->revokeLoginState();

        return [
            'template' => 'logout',
            'data' => ['logout'=>true, 'message'=>"You're now logged out."]
        ];
    }

    private function stateCancel() {
        if ($this->cancelSubscription()) {
            $this->revokeLoginState();
            return [
                'template' => 'logout',
                'data' => ['logout'=>true]
            ];

        } else {
            return [
                'template' => 'account_settings',
                'data' => ['form_result'=>"Your shipping address was updated successfully!"]
            ];
        }
    }

    /**
     * Helper function to set session vars for logins
     */
    private function setLoginState() {
        // this person has a password already, so we should probably make sure session is set

        $this->sessionSet("user_id", $this->user_id);
        $this->sessionSet("plan_id", $this->plan_id);
        $this->sessionSet("subscription_authenticated", true);
        $this->sessionSet('subscription_id', $this->subscription_id);

        $this->sessionSet("logged_in", true);

    }

}