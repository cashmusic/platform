<?php

namespace CASHMusic\Plants\Commerce\Stems;

use CASHMusic\Entities\CommerceTransaction;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;

use Exception;

trait Transactions {

    protected function addTransaction(
        $user_id,
        $connection_id,
        $connection_type,
        $service_timestamp='',
        $service_transaction_id='',
        $data_sent='',
        $data_returned='',
        $successful=-1,
        $gross_price=0,
        $service_fee=0,
        $status='abandoned',
        $currency='USD',
        $parent='order',
        $parent_id='0'
    ) {

        try {
            $transaction = $this->orm->create(CommerceTransaction::class, [
                'user_id' => $user_id,
                'connection_id' => $connection_id,
                'connection_type' => $connection_type,
                'service_timestamp' => $service_timestamp,
                'service_transaction_id' => $service_transaction_id,
                'data_sent' => json_encode($data_sent),
                'data_returned' => json_encode($data_returned),
                'successful' => $successful,
                'gross_price' => $gross_price,
                'service_fee' => $service_fee,
                'currency' => $currency,
                'status' => $status,
                'parent' => $parent,
                'parent_id' => $parent_id
            ]);
        } catch (Exception $e) {
            CASHSystem::errorLog($e->getMessage());
            return false;
        }

        if ($transaction) {
            return $transaction;
        } else {
            return false;
        }
    }

    protected function getTransaction($id,$user_id=false) {
        $conditions = array(
            "id" => $id
        );
        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        $transaction = $this->orm->findWhere(CommerceTransaction::class, $conditions);

        if ($transaction) {
            return $transaction;
        } else {
            return false;
        }
    }

    protected function editTransaction(
        $id,
        $service_timestamp=false,
        $service_transaction_id=false,
        $data_sent=false,
        $data_returned=false,
        $successful=false,
        $gross_price=false,
        $service_fee=false,
        $status=false
    ) {
        $final_edits = array_filter(
            array(
                'service_timestamp' => $service_timestamp,
                'service_transaction_id' => $service_transaction_id,
                'data_sent' => $data_sent,
                'data_returned' => $data_returned,
                'successful' => $successful,
                'gross_price' => $gross_price,
                'service_fee' => $service_fee,
                'status' => $status
            ),
            function($value) {
                return CASHSystem::notExplicitFalse($value);
            }
        );

        $transaction = $this->orm->find(CommerceTransaction::class, $id );

        if ($transaction->update($final_edits)) {
            return $transaction;
        } else {
            return false;
        }
    }

    protected function parseTransactionData($data_returned,$data_sent) {
        if (!is_array($data_returned)) {
            $data_returned = json_decode($data_returned,true);
        }
        if (!is_array($data_sent)) {
            $data_sent = json_decode($data_sent,true);
        }


        if (is_array($data_returned)) {

            if (isset($data_returned['customer_name']) && isset($data_returned['total'])) {

                return $data_returned;
            }
        }

        // LEGACY TRANSACTION
        if (is_array($data_sent)) {
            if (isset($data_sent['PAYMENTREQUEST_0_DESC'])) {
                if (isset($data_sent['PAYMENTREQUEST_0_DESC'])) {
                    $return_array = array(
                        'transaction_description' => $data_sent['PAYMENTREQUEST_0_DESC'],
                        'customer_email' => $data_sent['EMAIL'],
                        'customer_first_name' => $data_sent['FIRSTNAME'],
                        'customer_last_name' => $data_sent['LASTNAME'],
                        'customer_name' => $data_sent['FIRSTNAME'] . ' ' . $data_sent['LASTNAME']
                    );
                    // this is ugly, but the if statements normalize Paypal's love of omitting empty data
                    if (isset($data_sent['PAYMENTREQUEST_0_SHIPTONAME'])) {
                        $return_array['customer_shipping_name'] = $data_sent['PAYMENTREQUEST_0_SHIPTONAME'];
                    } else {
                        $return_array['customer_shipping_name'] = '';
                    }


                    if (isset($data_sent['PAYMENTREQUEST_0_SHIPTOSTREET'])) {
                        $return_array['customer_address1'] = $data_sent['PAYMENTREQUEST_0_SHIPTOSTREET'];
                    } else {
                        $return_array['customer_address1'] = '';
                    }
                    if (isset($data_sent['PAYMENTREQUEST_0_SHIPTOSTREET2'])) {
                        $return_array['customer_address2'] = $data_sent['PAYMENTREQUEST_0_SHIPTOSTREET2'];
                    } else {
                        $return_array['customer_address2'] = '';
                    }
                    if (isset($data_sent['PAYMENTREQUEST_0_SHIPTOCITY'])) {
                        $return_array['customer_city'] = $data_sent['PAYMENTREQUEST_0_SHIPTOCITY'];
                    } else {
                        $return_array['customer_city'] = '';
                    }
                    if (isset($data_sent['PAYMENTREQUEST_0_SHIPTOSTATE'])) {
                        $return_array['customer_region'] = $data_sent['PAYMENTREQUEST_0_SHIPTOSTATE'];
                    } else {
                        $return_array['customer_region'] = '';
                    }
                    if (isset($data_sent['PAYMENTREQUEST_0_SHIPTOZIP'])) {
                        $return_array['customer_postalcode'] = $data_sent['PAYMENTREQUEST_0_SHIPTOZIP'];
                    } else {
                        $return_array['customer_postalcode'] = '';
                    }
                    if (isset($data_sent['SHIPTOCOUNTRYNAME'])) {
                        $return_array['customer_country'] = $data_sent['SHIPTOCOUNTRYNAME'];
                    } else {
                        $return_array['customer_country'] = '';
                    }
                    if (isset($data_sent['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'])) {
                        $return_array['customer_countrycode'] = $data_sent['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'];
                    } else {
                        $return_array['customer_countrycode'] = '';
                    }
                    if (isset($data_sent['PAYMENTREQUEST_0_SHIPTOPHONENUM'])) {
                        $return_array['customer_phone'] = $data_sent['PAYMENTREQUEST_0_SHIPTOPHONENUM'];
                    } else {
                        $return_array['customer_phone'] = '';
                    }
                    return $return_array;
                } else {
                    return false;
                }
            }
        }

        return false;
    }
}