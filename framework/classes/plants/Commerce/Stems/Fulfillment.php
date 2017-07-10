<?php

namespace CASHMusic\Plants\Commerce\Stems;

use CASHMusic\Entities\CommerceExternalFulfillmentOrder;
use CASHMusic\Entities\CommerceExternalFulfillmentTier;

use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;

trait Fulfillment {
    protected function editFulfillmentOrder(
        $id,
        $name=false,
        $email=false,
        $shipping_address_1=false,
        $shipping_address_2=false,
        $shipping_city=false,
        $shipping_province=false,
        $shipping_postal=false,
        $shipping_country=false,
        $complete=false,
        $fulfilled=false,
        $price=false,
        $tier_id=false,
        $order_data=false,
        $notes=false,
        $data_sent=false
    ) {
        $final_edits = array_filter(
            array(
                'name' => $name,
                'email' => $email,
                'shipping_address_1' => $shipping_address_1,
                'shipping_address_2' => $shipping_address_2,
                'shipping_city' => $shipping_city,
                'shipping_province' => $shipping_province,
                'shipping_postal' => $shipping_postal,
                'shipping_country' => $shipping_country,
                'complete' => $complete,
                'fulfilled' => $fulfilled,
                'price' => $price,
                'tier_id' => $tier_id,
                'order_data' => $order_data,
                'notes' => $notes
            ),
            function($value) {
                return CASHSystem::notExplicitFalse($value);
            }
        );

        $fulfillment_order = $this->orm->find(CommerceExternalFulfillmentOrder::class, $id );

        if ($fulfillment_order->update($final_edits)) {
            return $fulfillment_order;
        } else {
            return false;
        }
    }

    protected function getFulfillmentOrder($id,$user_id=false) {

        $conditions = array(
            "id" => $id
        );

        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        if ($fulfillment_order = CommerceExternalFulfillmentOrder::findWhere($conditions)) {
            if (is_array($fulfillment_order)) {
                return $fulfillment_order[0];
            } else {
                return $fulfillment_order;
            }
        } else {
            return false;
        }
    }

    protected function getFulfillmentJobByTier($tier_id) {

        if ($tier = CommerceExternalFulfillmentTier::find($tier_id)) {
            if ($job = $tier->job()) {
                if (is_array($job)) {
                    return $job[0];
                }
                return $job;
            }
            return false;
        }
        return false;
    }
}