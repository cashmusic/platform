<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 1/25/17
 * Time: 3:32 PM
 */

namespace CASHMusic\Elements\subscription;

use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Core\ElementBase;
use CASHMusic\Elements\Interfaces\StatesInterface;
use CASHMusic\Entities\EntityBase;
use CASHMusic\Plants\Commerce\CommercePlant;
use CASHMusic\Elements\Subscription\ElementData;

class ElementState implements StatesInterface
{
    protected $state, $element_data, $element_id, $session, $session_id, $user_id, $plan_id, $email_address, $element_user_id;

    /**
     * States constructor. Set the needed values for whatever we're going to do to
     * react to the element state
     *
     * @param $element_data
     * @param $session_id
     */
    public function __construct($element_data, $session_id)
    {
        $this->state = !empty($_REQUEST['state']) ? $_REQUEST['state'] : "default";

        $this->element_data = $element_data;

        $this->session = new CASHRequest(null);
        $this->session->startSession($session_id);

        $this->session_id = $session_id;

        if (!$this->element_data['subscriber_id'] = $this->session->sessionGet("subscription_id")) {
            $this->element_data['subscriber_id'] = false;
        }

        if (!$this->element_data['email_address'] = $this->session->sessionGet("email_address")) {
            $this->element_data['email_address'] = false;
        }

        if (!$plan_id = $this->session->sessionGet("plan_id")) {
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

        if (!empty($this->element_data['subscriber_id'])) {
            $authenticated = true;
        }
        // if we're logged in already, maybe show them a logout button
        if (in_array($plan_id, $this->element_data['plans']) && $authenticated || $this->session->sessionGet('logged_in')) {
            $this->element_data['logged_in'] = true;
        }

        //TODO: this is also a problem if someone wants one plan to not be flexible price
        $this->element_data['flexible_price'] = false;

        foreach($this->element_data['all_plans'] as $plan) {
            if ($plan['flexible_price'] == 1) $this->element_data['flexible_price'] = true;
        }

        // check if $_REQUEST['key'] is set and do verify-y things
        $this->updateElementData(
            $this->processVerificationKey()
        );

        $this->session_id = $session_id;
        $this->element_id = $this->element_data['element_id'];

        $this->user_id = false;

        if (!empty($this->element_data['subscriber_id'])) {
            $this->user_id = $this->element_data['subscriber_id'];
        } else {
            if ($session_user_id = $this->session->sessionGet("subscription_id")) $this->user_id = $session_user_id;
        }

        $this->plan_id = $plan_id;
        $this->email_address = $this->element_data['email_address'];
        $this->element_user_id = $this->element_data['user_id'];
    }

    /**
     * State router. Ideally this will have a switch/case based on $_REQUEST['state'] that
     * returns an array with template name and data. Data is merged into the element_data array.
     *
     * [
     * 'template' => 'default',
     * 'data' => [...]
     * ]
     *
     * @param $callback
     * @return array
     */

    public function router($callback) {
        if (!empty($this->state)) {

            $result = [
                'template' => 'default',
                'data' => []
            ];

            switch ($this->state) {

                case "login":
                    $result = $this->stateLogin();
                    break;

                case "logout":
                    $result = $this->stateLogout();
                    break;

                case "success":
                    $result = $this->stateSuccess();
                break;

                case "verified":
                    $result = $this->stateVerified();
                    break;

                case "set_credentials":
                    $result = $this->stateSetCredentials();
                    break;

                case "validate_login":
                    $result = $this->stateValidateLogin();
                    break;

                case "logged_in_index":
                    $result = $this->stateLoggedInIndex();
                    break;

                case "account_settings":
                    $result = $this->stateAccountSettings();
                    break;

                case "account_address":
                    $result = $this->stateEditAddress();
                    break;

                case "forgot_password":
                    $result = $this->stateForgotPassword();
                    break;

                case "reset_password":
                    $result = $this->stateResetPassword();
                    break;

            }

            // merge in all data we have
            if (!empty($result['data'])) {
                $result['data'] = array_merge($this->element_data, $result['data']);
            } else {
                $result['data'] = $this->element_data;
            }

            $callback($result['template'], $result['data']);
        }
    }

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
        $subscriber_id = $this->session->sessionGet('subscription_id');
        $data['email'] = $this->session->sessionGet("email_address");
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
                $this->user_id = $password_request->response['payload'];

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

        $address = false;

        $subscriber_details = $this->getSubscriberDetails();

        if (is_cash_model($subscriber_details)) {
            if (isset($subscriber_details->data['shipping_info'])) {
                $address = $subscriber_details->data['shipping_info'];
            }
        }

        return [
            'template' => 'account/main',
            'data' => compact('address')
        ];
    }

    private function stateEditAddress() {

        $address = false;

        if (!isset($_REQUEST['action'])) {
            $subscriber_details = $this->getSubscriberDetails();

            if (is_cash_model($subscriber_details)) {
                $address = $subscriber_details->data;
            }
        }

        return [
            'template' => ['account/partials/setting_header', 'account/address'],
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
            'data' => ['logout'=>true]
        ];
    }

    /**
     * Helper function to set session vars for logins
     */
    private function setLoginState() {
        // this person has a password already, so we should probably make sure session is set

        $this->session->sessionSet("user_id", $this->user_id);
        $this->session->sessionSet("plan_id", $this->plan_id);
        $this->session->sessionSet("subscription_authenticated", true);

        $this->session->sessionSet("logged_in", true);

    }

    public function updateElementData($data) {
        if (is_array($data) && count($data) > 0) {
            $this->element_data = array_merge($this->element_data, $data);
        }
    }

    private function processVerificationKey() {

        $data = [];

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

                $data['key'] = true;
                $email = isset($_REQUEST['address']) ? $_REQUEST['address'] : "";

                if (empty($email)) {
                    $data['error_message'] = "Something went wrong.";

                    return $data;
                }

                $user_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'people',
                        'cash_action' => 'getuseridforaddress',
                        'address' => $email
                    )
                );

                $data['email_address'] = $email;
                $this->session->sessionSet("email_address", $email);

                if ($user_request->response['payload']) {
                    //$data['user_id'] = $user_request->response['payload'];
                    $data['subscriber_id'] = $user_request->response['payload'];
                    $this->session->sessionSet("subscription_id", $data['subscriber_id']);

                    //$this->element_data['subscriber_id'] = $user_request->response['payload'];
                } else {
                    $data['error_message'] = "We couldn't find your user.";
                }
            } else {
                $data['error_message'] = "Something went wrong.";
            }

            return $data;
        }
    }

    /**
     * @return bool
     */
    private function checkLoginState()
    {
        if (!$this->session->sessionGet("logged_in")) {
            return false;
        }

        return true;
    }

    private function revokeLoginState()
    {
        $this->session->sessionClear("subscription_id");
        $this->session->sessionClear('logged_in');
    }

    /**
     * @return mixed
     */
    private function getSubscriberDetails()
    {
        $address_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'getsubscriptiondetails',
                'id' => $this->session->sessionGet('subscription_id')
            )
        );

        CASHSystem::errorLog($this->session->sessionGet('subscription_id'));
        CASHSystem::errorLog($address_request->response);

        if ($address_request->response['payload'] !== false) {
            return $address_request->response['payload'];
        }

        return false;

    }

}