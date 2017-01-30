<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 1/25/17
 * Time: 3:32 PM
 */

namespace Cashmusic\Elements\subscription;


class States
{
    protected $state;

    public function __construct($plan_id, $user_id)
    {
        $this->state = $_REQUEST['state'];
        $this->user_id = $user_id;
        $this->plan_id = $plan_id;
    }

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

                default:
                    break;
            }

            $callback($result['template'], $result['data']);
        }
    }

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

        $data['has_password'] = false;

        if ($user_request->response['payload']) {

            if ($user_request->response['payload']['is_admin']) {
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
                'password' => $_REQUEST['password'],
                'is_admin' => true
            )
        );

        if ($password_request->response['payload'] !== false) {
            $template = 'logged_in_index';
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

        $plan_id = (isset($_REQUEST['plan_id'])) ? $_REQUEST['plan_id'] : false;

        $password_request = new \CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'loginsubscriber',
                'email' => $email,
                'password' => $password,
                'plan_id' => $plan_id
            )
        );

        if ($password_request->response['payload']) {
            // valid login + valid subscription
            if ($password_request->response['payload'] == "200") {

                // we need to make sure this is isolated by subscription---
                // maybe later we can actually have subscriptions switchable
                $this->setLoginState();
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

    private function stateLoggedInIndex() {

        return [
            'template' => 'logged_in_index',
            'data' => []
        ];
    }

    private function setLoginState() {
        // this person has a password already, so we should probably make sure session is set
        $session = new \CASHRequest(null);

        if (empty($session->sessionGet('user_id'))) $session->sessionSet("user_id", $this->user_id);
        if (empty($session->sessionGet('plan_id'))) $session->sessionSet("plan_id", $this->plan_id);
        if (empty($session->sessionGet('subscription_authenticated'))) $session->sessionSet("subscription_authenticated", true);

    }
}