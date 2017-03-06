<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 1/25/17
 * Time: 3:32 PM
 */

namespace Cashmusic\Elements\subscription;

use Cashmusic\Elements\Interfaces\StatesInterface;

class States implements StatesInterface
{
    protected $state;

    /**
     * States constructor. Set the needed values for whatever we're going to do to
     * react to the element state
     *
     * @param $element_data
     * @param $session_id
     * @param $element_id
     * @param $user_id
     * @param $element_user_id
     * @param $plan_id
     * @param $email_address
     */
    public function __construct($element_data, $element_id, $user_id, $element_user_id, $plan_id, $session_id)
    {
        $this->state = !empty($_REQUEST['state']) ? $_REQUEST['state'] : "default";

        $this->element_data = $element_data;

        if (!empty($session_id)) {
            $this->session = new \CASHRequest(null);
            $this->session->startSession($session_id);
        }

        $this->session_id = $session_id;

        $this->element_id = $element_id;
        $this->user_id = $this->element_data['subscriber_id'];
        $this->plan_id = $plan_id;
        $this->email_address = $this->element_data['email_address'];
        $this->element_user_id = $element_user_id;
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

                case "forgot_password":
                    $result = $this->stateForgotPassword();
                    break;

                case "reset_password":
                    $result = $this->stateResetPassword();
                    break;

                default:

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
        return [
            'template' => 'success',
            'data' => []
        ];
    }

    private function stateVerified() {

        $data = [];

        $user_request = new \CASHRequest(
            array(
                'cash_request_type' => 'people',
                'cash_action' => 'getuser',
                'user_id' => $this->user_id
            )
        );

        error_log("stateVerified /".$this->user_id . json_encode($user_request));

        if ($user_request->response['payload']) {

            if ($user_request->response['payload']['is_admin'] == 1) {
                $data['has_password'] = true;

                $this->setLoginState();
                $data['logged_in'] = true;
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
        $password_request = new \CASHRequest(
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
            $data['logged_in'] = true;


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

        $password_request = new \CASHRequest(
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
                $data['logged_in'] = true;

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

        $items = [];

        if (!empty($this->element_data['items'])) {

            // get feed items so we can add some stuff
            foreach($this->element_data['items'] as $item) {
                $details = Data::getItemDetails($item['item_id'], $this->session_id);

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

        $element_request = new \CASHRequest(
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

        if (!\CommercePlant::sendResetValidationEmail(
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

    /**
     * Helper function to set session vars for logins
     */
    private function setLoginState() {
        // this person has a password already, so we should probably make sure session is set

        $this->session->sessionSet("user_id", $this->user_id);
        $this->session->sessionSet("plan_id", $this->plan_id);
        $this->session->sessionSet("subscription_authenticated", true);

    }

}