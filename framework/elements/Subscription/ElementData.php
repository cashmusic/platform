<?php

namespace CASHMusic\Elements\Subscription;

use CASHMusic\Elements\Interfaces\DataInterface;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Seeds\StripeSeed;
use ArrayIterator;

class ElementData implements DataInterface
{
    protected $user_id, $plan_id;
    public $data;
    
    public function __construct($user_id)
    {
        $this->data = [];
        $this->user_id = $user_id;
        $this->plan_id = false;

        $this->data['public_url'] = CASH_PUBLIC_URL;

        // payment connection settings
        $this->data['paypal_connection'] = false;
        $this->data['stripe_public_key'] = false;
        $this->data['verification'] = false;

        $this->data['logged_in'] = false;
    }

    /**
     * Various methods for getting data and returning it to the main element controller.
     *
     * @return $this
     */

    public function getCurrency() {
        $currency_request = new CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'getsettings',
                'type' => 'use_currency',
                'user_id' => $this->user_id
            )
        );

        // currency stuff
        if ($currency_request->response['payload']) {
            $this->data['currency'] = CASHSystem::getCurrencySymbol($currency_request->response['payload']);
        } else {
            $this->data['currency'] = CASHSystem::getCurrencySymbol('USD');
        }

        return $this->data;
    }


    public function getConnections() {
        $settings_request = new CASHRequest(
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
                    $payment_seed = new StripeSeed($this->user_id,$settings_request->response['payload']['stripe_default']);
                    if (!empty($payment_seed->publishable_key)) {
                        $this->data['stripe_public_key'] = $payment_seed->publishable_key;
                    }
                }
            }
        } else {
            if (isset($this->data['connection_id'])) {
                $connection_settings = CASHSystem::getConnectionTypeSettings($this->data['connection_type']);
                $seed_class = $connection_settings['seed'];
                if ($seed_class == 'StripeSeed') {
                    $payment_seed = new StripeSeed($this->user_id,$this->data['connection_id']);
                    if (!empty($payment_seed->publishable_key)) {
                        $this->data['stripe_public_key'] = $payment_seed->publishable_key;
                    }
                }
            }
        }

        return $this->data;
    }

    public function getPlan($plan_id) {
        // get plan data
        $plan_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'getsubscriptionplan',
                'user_id' => $this->user_id,
                'id' => $plan_id
            )
        );

        // get plan data or bust
        if ($plan_request->response['payload']) {

            $payload = $plan_request->response['payload']->toArray();

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

        return $this->data;
    }

    /**
     * Get details for items in the subscription feed.
     *
     * @param $item_id
     * @return array|bool
     */

    public static function getItemDetails($item_id, $session_id) {

        $item_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'getitem',
                'id' => $item_id
            )
        );

        $item = $item_request->response['payload'];

        $item['asset'] = $item['fulfillment_asset'];

        if (!empty($item['descriptive_asset'])) {
            $item_image_request = new CASHRequest(
                array(
                    'cash_request_type' => 'asset',
                    'cash_action' => 'getpublicurl',
                    'id' => $item['descriptive_asset']
                )
            );
            $item['item_image_url'] = $item_image_request->response['payload'];
        } else {
            $item['item_image_url'] = false;
        }

        if (!empty($item['fulfillment_asset'])) {
            $fulfillment_request = new CASHRequest(
                array(
                    'cash_request_type' => 'asset',
                    'cash_action' => 'getfulfillmentassets',
                    'asset_details' => $item['fulfillment_asset'],
                    'session_id' => $session_id
                )
            );

            if ($fulfillment_request->response['payload']) {
                $item['fulfillment_assets'] = new ArrayIterator($fulfillment_request->response['payload']);
                $assets = [];
                foreach($item['fulfillment_assets'] as $asset) {
                    unset($asset['public_url']);

                    $assets[] = $asset;
                }

                $item['fulfillment_assets'] = $assets;

            }

            if (!empty($item['fulfillment_assets']) && !empty($item['item_image_url']))  {
                $item['has_image_and_download'] = true;
            }
        }

        if (!empty($item)) {
            unset($item['title']);
            return $item;

        } else {
            return false;
        }

    }
}