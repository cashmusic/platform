<?php

namespace CASHMusic\Elements\Subscription\Extensions;

trait Router {
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

}