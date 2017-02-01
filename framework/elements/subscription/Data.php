<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 1/25/17
 * Time: 1:57 PM
 */

namespace Cashmusic\Elements\subscription;


class Data
{
    protected $user_id, $plan_id;
    public $data;
    
    public function __construct($user_id, $plan_id)
    {
        $this->data = [];
        $this->user_id = $user_id;
        $this->plan_id = $plan_id;

        $this->data['public_url'] = CASH_PUBLIC_URL;

        // payment connection settings
        $this->data['paypal_connection'] = false;
        $this->data['stripe_public_key'] = false;
        $this->data['verification'] = false;

        $this->data['logged_in'] = false;

        $this->getData();
    }

    public function getCurrency() {
        $currency_request = new \CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'getsettings',
                'type' => 'use_currency',
                'user_id' => $this->user_id
            )
        );

        // currency stuff
        if ($currency_request->response['payload']) {
            $this->data['currency'] = \CASHSystem::getCurrencySymbol($currency_request->response['payload']);
        } else {
            $this->data['currency'] = \CASHSystem::getCurrencySymbol('USD');
        }

        return $this;
    }

    public function getPlan() {
        // get plan data
        $plan_request = new \CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'getsubscriptionplan',
                'user_id' => $this->user_id,
                'id' => $this->plan_id
            )
        );

        // get plan data or bust
        if ($plan_request->response['payload'] && !empty($plan_request->response['payload'][0])) {

            $payload = $plan_request->response['payload'][0];

            $this->data['plan_name'] = $payload['name'];
            $this->data['interval'] = $payload['interval'];
            $this->data['plan_description'] = $payload['description'];
            $this->data['flexible_price'] = $payload['flexible_price'];

            $this->data['plan_price'] = $payload['price'];

            // if flexible pricing is set let's set the default to suggested price
            if (!empty($this->data['flexible_price'])) {
                $this->data['plan_price'] = $payload['suggested_price'];
                $this->data['minimum_price'] = $payload['price'];
            } else {
                $this->data['minimum_price'] = $this->data['plan_price'];
            }

            $this->data['plan_interval'] = $payload['interval'];
            $this->data['plan_id'] = $payload['id'];

            $this->data['plan_flexible_price'] = ($payload['flexible_price'] == 1) ? true: false;

            $this->data['shipping'] = ($payload['physical'] == 0) ? "false": "true";

        }

        return $this;
    }

    public function getConnections() {
        $settings_request = new \CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'getsettings',
                'type' => 'payment_defaults',
                'user_id' => $this->user_id
            )
        );

        if (is_array($settings_request->response['payload'])) {

            if (isset($settings_request->response['payload']['stripe_default'])) {
                if ($settings_request->response['payload']['stripe_default']) {
                    $payment_seed = new \StripeSeed($this->user_id,$settings_request->response['payload']['stripe_default']);
                    if (!empty($payment_seed->publishable_key)) {
                        $this->data['stripe_public_key'] = $payment_seed->publishable_key;
                    }
                }
            }
        } else {
            if (isset($this->data['connection_id'])) {
                $connection_settings = \CASHSystem::getConnectionTypeSettings($this->data['connection_type']);
                $seed_class = $connection_settings['seed'];
                if ($seed_class == 'StripeSeed') {
                    $payment_seed = new \StripeSeed($this->user_id,$this->data['connection_id']);
                    if (!empty($payment_seed->publishable_key)) {
                        $this->data['stripe_public_key'] = $payment_seed->publishable_key;
                    }
                }
            }
        }

        return $this;
    }

    public function getData() {

        $this->getConnections()
             ->getCurrency()
             ->getPlan();

        return $this->data;
    }
}