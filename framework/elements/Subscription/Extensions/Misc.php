<?php

namespace CASHMusic\Elements\Subscription\Extensions;

use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;

trait Misc {
    private function processVerificationKey() {

        $data = [];
        $email = isset($_REQUEST['address']) ? $_REQUEST['address'] : "";
        $key = isset($_REQUEST['key']) ? $_REQUEST['key'] : "";

        if (!isset($email, $key)) return false; // how did we even get here?

        $this->sessionSet("email_address", $email);

        if (!empty($_REQUEST['key'])) {
            $validate_request = new CASHRequest(
                array(
                    'cash_request_type' => 'system',
                    'cash_action' => 'validateresetflag',
                    'address' => $email,
                    'key' => $key
                )
            );

            if ($validate_request->response['payload']) {
                $data['key'] = true;

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

                if ($user_request->response['payload']) {
                    //$data['user_id'] = $user_request->response['payload'];
                    $data['subscription_id'] = $user_request->response['payload'];
                    $this->sessionSet("subscription_id", $user_request->response['payload']);

                    //$this->element_data['subscription_id'] = $user_request->response['payload'];
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
        if (!$this->sessionGet("logged_in")) {
            return false;
        }

        return true;
    }

    private function revokeLoginState()
    {
        $this->sessionClear("subscription_id");
        $this->sessionClear('logged_in');
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
                'id' => $this->sessionGet('subscription_id'),
                'user_id'=>true
            )
        );

        if ($address_request->response['payload'] !== false) {

            if (!$subscriber_id = $this->element_data['subscription_id']) {
                $subscriber_id = $this->sessionGet('subscription_id');
            }

            $payment_details_request = new CASHRequest(
                array(
                    'cash_request_type' => 'commerce',
                    'cash_action' => 'getsubscriberpaymentdetails',
                    'subscriber_id' => $subscriber_id,
                    'user_id'=>$this->element_user_id
                )
            );

            CASHSystem::errorLog($payment_details_request);

            if ($payment = $payment_details_request->response['payload']){

                //$payment['payment'] = $address_request->response['payload'];

                return $payment;
            }
        }

        return false;

    }

    public function checkRequestForFormSubmission() {
        if (isset($_REQUEST['action'])) {
            if ($_REQUEST['action'] == "update_address") {

                $address = [
                    'customer_shipping_name' => trim($_REQUEST['name']),
                    'customer_address1' => trim($_REQUEST['address1']),
                    'customer_address2' => trim($_REQUEST['address2']),
                    'customer_city' => trim($_REQUEST['city']),
                    'customer_region' => trim($_REQUEST['region']),
                    'customer_postalcode' => trim($_REQUEST['postalcode']),
                    'customer_countrycode' => trim($_REQUEST['country'])
                ];

                if (!$subscriber_id = $this->element_data['subscription_id']) {
                    $subscriber_id = $_REQUEST['subscription_id'];
                }

                $address_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'commerce',
                        'cash_action' => 'updatesubscriptionaddress',
                        'subscriber_id' => $subscriber_id,
                        'address' => $address
                    )
                );

                $this->element_data['form_result'] = false;

                if ($address_request->response['payload']) {
                    $this->element_data['form_result'] = "Your shipping address was updated successfully!";
                }

                $this->state = "account_settings";
            }
        }
    }
}