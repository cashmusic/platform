<?php
/**
 * CommercePlant manages products/offers/orders, records transactions, and
 * deals with payment processors
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 * This file is generously sponsored by Devin Palmer | www.devinpalmer.com
 *
 **/

namespace CASHMusic\Plants\Commerce;

use CASHMusic\Plants\Commerce\Stems\Fulfillment;
use CASHMusic\Plants\Commerce\Stems\Items;
use CASHMusic\Plants\Commerce\Stems\Subscriptions;
use CASHMusic\Plants\Commerce\Stems\Transactions;
use CASHMusic\Plants\Commerce\Stems\Variants;

use CASHMusic\Core\PlantBase;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Entities\CommerceOrder;

use CASHMusic\Seeds\PaypalSeed;
use CASHMusic\Seeds\StripeSeed;
use CASHMusic\Admin\AdminHelper;
use Exception;

class CommercePlant extends PlantBase {

    // break out functionality to traits to better manage feature subsets
    use Fulfillment;
    use Items;
    use Subscriptions;
    use Transactions;
    use Variants;

    protected $subscription_active_status, $request_type, $routing_table;

    public function __construct($request_type,$request) {

        $this->request_type = 'commerce';
        $this->getRoutingTable();

        $this->plantPrep($request_type,$request);

        $this->subscription_active_status = ['active', 'comped'];
    }

    protected function addToCart($item_id,$element_id,$item_variant=false,$price=false,$session_id=false) {
         $r = new CASHRequest();
         $r->startSession($session_id);

         $cart = $r->sessionGet('cart');
         if (!$cart) {
            $cart = array(
               $element_id => array(
                  'shipto' => ''
               )
            );
         } else {
            if (!isset($cart[$element_id])) {
               $cart[$element_id] = array(
                  'shipto' => ''
               );
            }
         }
         $qty = 1;
         if (isset($cart[$element_id][$item_id.$item_variant])) {
            $qty = $cart[$element_id][$item_id.$item_variant]['qty'] + 1;
         }
         $cart[$element_id][$item_id.$item_variant] = array(
            'id' 		 	 => $item_id,
            'variant' 	 => $item_variant,
            'price' 		 => $price,
            'qty'		 	 => $qty
         );

         $r->sessionSet('cart', $cart);
         return $cart[$element_id];
    }

    protected function editCartQuantity($item_id,$element_id,$qty,$item_variant='',$session_id=false) {
        $r = new CASHRequest();
        $r->startSession($session_id);

        $cart = $r->sessionGet('cart');
        if (!$cart) {
            return false;
        } else {
           if (!isset($cart[$element_id])) {
             return false;
           }
        }

        if (!isset($cart[$element_id][$item_id.$item_variant])) {
            return false;
        } else {
            if ($qty == 0) {
                unset($cart[$element_id][$item_id.$item_variant]);
            } else {
                $cart[$element_id][$item_id.$item_variant]['qty'] = $qty;
            }
            $r->sessionSet('cart', $cart);
            return $cart[$element_id];
        }
    }

    protected function editCartShipping($element_id,$region='r1',$session_id=false) {
        $r = new CASHRequest();
        $r->startSession($session_id);

        $cart = $r->sessionGet('cart');
        if (!$cart) {
            return false;
        } else {
           if (!isset($cart[$element_id])) {
             return false;
           }
        }

        $cart[$element_id]['shipto'] = $region;
        $r->sessionSet('cart', $cart);
        return $cart[$element_id];
    }

    protected function emptyCart($element_id,$session_id=false) {
         $r = new CASHRequest();
         $r->startSession($session_id);
         $cart = $r->sessionGet('cart');
         if ($cart) {
            if (isset($cart[$element_id])) {
               unset($cart[$element_id]);
               $r->sessionSet('cart', $cart);
            }
         }
         return true;
    }

    protected function getCart($element_id,$session_id=false) {
        $r = new CASHRequest();
        $r->startSession($session_id);
        $cart = $r->sessionGet('cart');
        if ($cart) {
           if (isset($cart[$element_id])) {
             return $cart[$element_id];
           }
        }
        return false;
    }

    protected function addOrder(
        $user_id,
        $order_contents,
        $transaction_id=-1,
        $physical=0,
        $digital=0,
        $cash_session_id='',
        $element_id=0,
        $customer_user_id=0,
        $fulfilled=0,
        $canceled=0,
        $notes='',
        $country_code='',
        $currency='USD',
        $data=''
    ) {
        if (is_array($order_contents)) {
            /*
                basically we store as JSON to prevent loss of order history
                in the event an item changes or is deleted. we want accurate
                history so folks don't get all crazy bananas about teh $$s
            */
            try {
                $order = $this->orm->create(CommerceOrder::class, [
                    'user_id' => $user_id,
                    'customer_user_id' => $customer_user_id,
                    'transaction_id' => $transaction_id,
                    'order_contents' => $order_contents,
                    'fulfilled' => $fulfilled,
                    'canceled' => $canceled,
                    'physical' => $physical,
                    'digital' => $digital,
                    'notes' => $notes,
                    'country_code' => $country_code,
                    'currency' => $currency,
                    'element_id' => $element_id,
                    'cash_session_id' => $cash_session_id,
                    'data' => $data
                ]);

            } catch (Exception $e) {
                CASHSystem::errorLog($e->getMessage());
                return false;
            }

            return $order->id;
        } else {
            return false;
        }
    }

    protected function getOrder($id,$deep=false,$user_id=false) {

        $conditions = array(
            "id" => $id
        );

        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        if($order = $this->orm->findWhere(CommerceOrder::class, $conditions)) {
            // cast a spell of summoning if this is an array. it never should be but
            if (is_array($order)) {
                $order = $order[0];
            }

            if (!$deep) $order = $order->toArray();

            if ($deep) {
                $transaction = $order->transaction();
                $order = $order->toArray();
                $order['order_totals'] = $this->getOrderTotals($order['order_contents']);
                $order['order_description'] = $order['order_totals']['description'];
                // currently there will only be one transaction for orders
                if ($transaction) {

                    $transaction_data = $this->parseTransactionData($transaction->data_returned, $transaction->data_sent);

                    $transaction_array = $transaction->toArray();

                    if (is_array($transaction_array)) {
                        $order = array_merge($transaction_array, $order);
                    }
                }

                if (is_array($transaction_data)) {
                    $order = array_merge($order,$transaction_data);
                }

                $user_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'people',
                        'cash_action' => 'getuser',
                        'user_id' => $order['customer_user_id']
                    )
                );
                $order['customer_details'] = $user_request->response['payload'];
            }

            return $order;
        }

        return false;


    }

    protected function editOrder(
        $id,
        $fulfilled=false,
        $canceled=false,
        $notes=false,
        $country_code=false,
        $customer_user_id=false,
        $order_contents=false,
        $transaction_id=false,
        $physical=false,
        $digital=false,
        $user_id=false,
        $data=''
    ) {
        $final_edits = array_filter(
            array(
                'transaction_id' => $transaction_id,
                'order_contents' => $order_contents,
                'fulfilled' => $fulfilled,
                'canceled' => $canceled,
                'physical' => $physical,
                'digital' => $digital,
                'notes' => $notes,
                'country_code' => $country_code,
                'customer_user_id' => $customer_user_id,
                'data' => $data
            ),
            function($value) {
                return CASHSystem::notExplicitFalse($value);
            }
        );

        $conditions = array(
            "id" => $id
        );

        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        $order = $this->orm->findWhere(CommerceOrder::class, $conditions);

        if ($order->update($final_edits)) {
            return $order->toArray();
        } else {
            return false;
        }
    }

    protected function getOrdersForUser($user_id,$include_abandoned=false,$max_returned=false,$since_date=0,$unfulfilled_only=0,$deep=false,$skip=0) {
        
        CASHSystem::errorLog(func_get_args());
        if ($max_returned) {
            $limit = $max_returned;
        } else {
            $limit = false;
        }


        try {
            // gets multiple orders with all information
            if (!$deep) {
                $query = $this->db->table('commerce_orders')->select('commerce_orders.*');
            } else if ($deep) {
                $query = $this->db->table('commerce_orders')->select([
                        'commerce_orders.*',
                        'commerce_transactions.data_returned',
                        'commerce_transactions.data_sent',
                        'commerce_transactions.successful',
                        'commerce_transactions.gross_price',
                        'commerce_transactions.service_fee',
                        'commerce_transactions.currency',
                        'commerce_transactions.status',
                        'commerce_transactions.service_transaction_id',
                        'commerce_transactions.service_timestamp',
                        'commerce_transactions.connection_type',
                        'commerce_transactions.connection_id'
                    ])->join('commerce_transactions', 'commerce_transactions.id', '=', 'commerce_orders.transaction_id')
                    ->join('people', 'people.id', '=', 'commerce_orders.customer_user_id')
                    ->where('commerce_transactions.successful', '=', 1);
            }

            $query = $query->where('commerce_orders.user_id', '=', $user_id);

            if ($since_date > 0) {
                $query = $query->where('commerce_orders.creation_date', ">", $since_date);
            }

            if ($include_abandoned) {
                $query = $query->where('commerce_orders.modification_date', ">", 0);
            }

            if (isset($unfulfilled_only)) {
                if ($unfulfilled_only == 1) {
                    $query = $query->where('commerce_orders.fulfilled', "<", $unfulfilled_only)->orderBy("commerce_orders.id", "ASC");
                } else {
                    $query = $query->orderBy("commerce_orders.id", "DESC");
                }
            }

            if ($limit) $query = $query->limit($limit)->offset($skip);

            $result = $query->get();
        } catch (Exception $e) {
            CASHSystem::errorLog($e->getMessage());
        }

        if ($deep) {
            if ($result) {
                // loop through and parse all transactions
                if (is_array($result)) {
                    foreach ($result as &$order) {
                        $order = json_decode(json_encode($order), true); // cast array wizard spell

                        if ($transaction_data = $this->parseTransactionData($order['data_returned'], $order['data_sent'])) {

                            if (is_array($transaction_data)) {
                                $order = array_merge($order, $transaction_data);
                            }

                            $order_totals = $this->getOrderTotals($order['order_contents']);
                            $order['order_description'] = $order_totals['description'];

                            if (!is_array($order['order_contents'])) {
                                $order['order_contents'] = json_decode($order['order_contents'], true);
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
        } else {
            if (is_array($result)) {
                foreach ($result as &$order) {
                    if (!is_array($order->order_contents)) {
                        $order->order_contents = json_decode($order->order_contents, true);
                    }

                    $order = json_decode(json_encode($order), true); // cast array wizard spell
                }
            }
        }

        if ($result) {
            return $result;
        } else {
            return false;
        }

    }

    protected function getOrdersByCustomer($user_id,$customer_email) {
        $user_request = new CASHRequest(
            array(
                'cash_request_type' => 'people',
                'cash_action' => 'getuseridforaddress',
                'address' => $customer_email
            )
        );
        $customer_id = $user_request->response['payload'];

        try {
            $orders = $this->orm->findWhere(CommerceOrder::class, ['user_id'=>$user_id, 'customer_user_id'=>$customer_id] );
        } catch (Exception $e) {
            CASHSystem::errorLog($e);
        }

        if ($orders) {
            return $orders;
        } else {
            return false;
        }
    }

    protected function getOrdersByItem($user_id,$item_id,$max_returned=false,$skip=0,$since_date=false) {

        if ($max_returned) {
            $limit = $max_returned;
        } else {
            $limit = false;
        }

        // gets multiple orders with all information
        $query = $this->db->table('commerce_orders')
            ->select('commerce_orders.*');

            $query = $query->select(
                [
                    'commerce_transactions.data_returned',
                    'commerce_transactions.data_sent',
                    'commerce_transactions.successful',
                    'commerce_transactions.gross_price',
                    'commerce_transactions.service_fee',
                    'commerce_transactions.currency',
                    'commerce_transactions.status',
                    'commerce_transactions.service_transaction_id',
                    'commerce_transactions.service_timestamp',
                    'commerce_transactions.connection_type',
                    'commerce_transactions.connection_id'
                ]
            );

            $query = $query->join('commerce_transactions', 'commerce_transactions.id', '=', 'commerce_orders.transaction_id');

        $query = $query->where('commerce_orders.user_id', '=', $user_id)
            ->where('commerce_transactions.successful', '=', 1);

        if ($since_date) {
            $query = $query->where('commerce_orders.creation_date', ">", $since_date);
        }

        if (isset($unfulfilled_only)) {
            if ($unfulfilled_only == 1) {
                $query = $query->where('commerce_orders.fulfilled', "<", $unfulfilled_only)->orderBy("commerce_orders.id", "ASC");
            } else {
                $query = $query->where('commerce_orders.fulfilled', ">=", $unfulfilled_only)->orderBy("commerce_orders.id", "DESC");
            }
        }

        $query = $query->where('commerce_orders.order_contents', 'LIKE', '%"id":"' . $item_id . '"%');

        if ($limit) $query = $query->limit($limit)->offset($skip);

        $result = $query->get();

        if ($result) {
            // loop through and parse all transactions
            if (is_array($result)) {
                foreach ($result as &$order) {
                    $transaction_data = $this->parseTransactionData($order['data_returned'],$order['data_sent']);
                    if (is_array($transaction_data)) {
                        $order = array_merge($order,$transaction_data);
                    }
                    $order_totals = $this->getOrderTotals($order['order_contents']);
                    $order['order_description'] = $order_totals['description'];
                }
            }
        }
        return $result;
    }

    /** initiateCheckout
     * @param bool $element_id
     * @param bool $shipping_info
     * @param bool $paypal
     * @param bool $stripe
     * @param bool $origin
     * @param bool $email_address
     * @param bool $customer_name
     * @param bool $session_id
     * @return bool
     */
    public function initiateCheckout($element_id=false,$shipping_info=false,$paypal=false,$stripe=false,$origin=false,$email_address=false,$customer_name=false,$session_id=false,$geo=false,$finalize_url=false) {

        //TODO: store last seen top URL
        //      or maybe make the API accept GET params? does it already? who can know?
        //$r = new CASHRequest();
        $this->startSession($session_id);
        if (!$element_id) {
            return false;
        } else {
            $is_physical = 0;
            $is_digital = 0;

            $user_id = CASHSystem::getUserIdByElement($element_id);

            $default_connections = CommercePlant::getDefaultConnections($user_id);

            if (is_array($default_connections)) {
              $pp_default = (!empty($default_connections['paypal'])) ? $default_connections['paypal'] : false;
              $pp_micro = (!empty($default_connections['paypal_micro'])) ? $default_connections['paypal_micro'] : false;
              $stripe_default = (!empty($default_connections['stripe'])) ? $default_connections['stripe'] : false;
            } else {
              return false; // no default PP shit set
            }

            $cart = $this->getCart($element_id,$session_id);

            $shipto = $cart['shipto'];
            unset($cart['shipto']);
            if ($shipto != 'r1' && $shipto != 'r2') {
              $shipto = 'r1';
            }
            $subtotal = 0;
            $shipping = 0;

            foreach ($cart as $key => &$i) {
              $item_details = $this->getItem($i['id'],false,false);
              $variants = $this->getItemVariants($i['id']);
              $item_details['qty'] = $i['qty'];
              $item_details['price'] = max($i['price'],$item_details['price']);
              $subtotal += $item_details['price']*$i['qty'];
              $item_details['variant'] = $i['variant'];

              if ($item_details['physical_fulfillment']) {
                  $is_physical = 1;
              }
              if ($item_details['digital_fulfillment']) {
                  $is_digital = 1;
              }
              if ($item_details['shipping'] && $shipto) {
                  if (isset($item_details['shipping']['r1-1'])) {
                      $shipping += $item_details['shipping'][$shipto.'-1+']*($i['qty']-1)+$item_details['shipping'][$shipto.'-1'];
                  }
              }
              if ($variants) {
                  foreach ($variants['quantities'] as $q) {
                      if ($q['key'] == $item_details['variant']) {
                          $item_details['variant_name'] = $q['formatted_name'];
                          break;
                      }
                  }
              }
              $order_contents[] = $item_details;
            }

            $total_price = $subtotal + $shipping;

            // total zero price stop-gap, before this thing can load seeds or create an order or any dumb stuff.
            if ($total_price == 0) {
                $this->unlockElement($element_id);
                return "success"; // and dance
            }

            /*
            // get connection type settings so we can extract Seed classname
            $connection_settings = CASHSystem::getConnectionTypeSettings($connection_type);
            $seed_class = $connection_settings['seed'];
            */

            //TODO: ultimately we want to load in the seed class name dynamically, but let's just get this working for now
            if ($stripe != false) {
              $seed_class = "StripeSeed";
              $connection_id = $stripe_default;

            }

            if ($paypal != false) {
              $seed_class = "PaypalSeed";
              //TODO: this connection stuff is hard-coded for paypal, but does the default/micro switch well
              if (($subtotal+$shipping < 12) && $pp_micro) {
                  $connection_id = $pp_micro;
              } else {
                  $connection_id = $pp_default;
              }
            }

            $seed_class = '\\CASHMusic\Seeds\\'.$seed_class;

            $currency = $this->getCurrencyForUser($user_id);

            // merge all this stuff into $data for storage
            if (!is_array($shipping_info)) $shipping_info = json_decode($shipping_info, true);

            $data = array("geo" => $geo);

            if ($shipping_info) {
                $data = array_merge($shipping_info, $data);
            }


            $transaction = $this->addTransaction(
                $user_id,
                $connection_id,
                $this->getConnectionType($connection_id),
                '',
                '',
                '',
                '',               # this is data_returned, dummy
                -1,
                $total_price, // set price
                0,
                'abandoned',
                $currency
            );

            $order_id = $this->addOrder(
                $user_id,
                $order_contents,
                $transaction->id,
                $is_physical,
                $is_digital,
                $this->getSessionID(),
                $element_id,
                0,
                0,
                0,
                '',
                '',
                $currency,
                $data
            );


            $shipping_info_formatted = array(
                'customer_shipping_name' => $shipping_info['name'],
                'customer_address1' => $shipping_info['address1'],
                'customer_address2' => $shipping_info['address2'],
                'customer_city' => $shipping_info['city'],
                'customer_region' => $shipping_info['state'],
                'customer_postalcode' => $shipping_info['postalcode'],
                'customer_countrycode' => $shipping_info['country']);

            $this->startSession($session_id);
            $this->sessionSet('shipping_info',$shipping_info_formatted);

            if ($order_id) {

                $order_details = $this->getOrder($order_id);

                $transaction = $this->getTransaction($order_details['transaction_id']);

                //TODO: we'll need to figure out a way to get the connection_id for whatever payment method was chosen, in order to switch on the fly
                $connection_type = $this->getConnectionType($transaction->connection_id);
                $order_totals = $this->getOrderTotals($order_details['order_contents']);

                // ascertain whether or not this seed requires a redirect, else let's cheese it right to the charge
                // we're going to switch seeds by $connection_type, so check to make sure this class even exists
                if (!class_exists($seed_class)) {
                    $this->setErrorMessage("1301 Couldn't find payment type $seed_class.");
                    return false;
                }

                // call the payment seed class
                $payment_seed = new $seed_class($user_id,$transaction->connection_id);

                // does this payment type need to redirect? if so let's do preparePayment and get a redirect URL
                if ($payment_seed->redirects != false) {
                    // prepare payment with URL redirect
                    $return_url = $origin . '?cash_request_type=commerce&cash_action=finalizepayment&order_id=' . $order_id . '&creation_date=' . $order_details['creation_date'];
                    if ($element_id) {
                        $return_url .= '&element_id=' . $element_id;
                    }
                    if ($session_id) {
                        $return_url .= '&session_id=' . $session_id;
                    }
                    if ($finalize_url) {
                       $return_url .= '&finalize_url=' . urlencode($finalize_url);
                    }

                    $approval_url = $payment_seed->preparePayment(
                        $total_price,							# payment amount
                        'order-' . $order_id,						# order id
                        $order_totals['description'],				# order name
                        $return_url,				# return URL
                        $origin,					# cancel URL (the same in our case)
                        $currency,									# payment currency
                        'Sale',										# transaction type (e.g. 'Sale', 'Order', or 'Authorization')
                        $shipping,								# price additions (like shipping, but could be taxes in future as well)
                        $transaction->id                         # for adding data sent
                    );

                    // returns a url, javascript parses for success/failure and gets http://, so it does a redirect
                    return $approval_url;
                } else {
                    // doPayment
                    //$order_details = $this->getOrder($order_id);

                    // javascript shows success or failure depending on what happens here
                    if ($result = $this->finalizePayment(
                        $order_id,
                        $stripe,
                        $email_address,
                        $customer_name,
                        $shipping_info,
                        $session_id,
                        $total_price,
                        $order_totals['description'],
                        $finalize_url)) {
                        return "success";
                    } else {
                        return "failure";
                    }
                }
                //$success = $this->initiatePaymentRedirect($order_id,$element_id,$price_addition,$url_only,$finalize_url,$session_id);
                //return $success;
            } else {
                return false;
            }
        }
    }

    protected function getOrderTotals($contents) {

        if (!is_array($contents)) { $contents = json_decode($contents, true); }

        if (!$contents) return false;

        $return_array = array(
            'price' => 0,
            'description' => ''
        );
        foreach($contents as $item) {
            if (!isset($item['qty'])) {
                $item['qty'] = 1;
            }
            $return_array['price'] += $item['price']*$item['qty'];
            if (isset($item['qty'])) {
                $return_array['description'] .= $item['qty'] . 'x ';
            }

            // checking physical fulfillment

            $return_array['description'] .= $item['name'];
            if (isset($item['variant'])) {
                if ($item['variant']) {

                    preg_match_all("/([a-z]+)->/", $item['variant'], $key_parts);

                    $variant_keys = $key_parts[1];
                    $variant_values = preg_split("/([a-z]+)->/", $item['variant'], 0, PREG_SPLIT_NO_EMPTY);
                    $count = count($variant_keys);

                    $variant_descriptions = array();

                    for($index = 0; $index < $count; $index++) {
                        $key = $variant_keys[$index];
                        $value = trim(str_replace('+', ' ', $variant_values[$index]));
                        $variant_descriptions[] = "$key: $value";
                    }

                    $return_array['description'] .= ' (' . implode(', ', $variant_descriptions) . ')';
                }
            }
            $return_array['description'] .= ",  \n";
        }


        $return_array['description'] = rtrim($return_array['description']," ,\n");
        return $return_array;
    }

    protected function getCurrencyForUser($user_id) {
        $currency_request = new CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'getsettings',
                'type' => 'use_currency',
                'user_id' => $user_id
            )
        );
        if ($currency_request->response['payload']) {
            $currency = $currency_request->response['payload'];
        } else {
            $currency = 'USD';
        }
        return $currency;
    }

    /**
     * $this->getOrderProperties
     *
     * Literally just returning the first index of this item, for legibility
     * @param string $order_contents
     * @return array or bool
     */
    protected function getOrderProperties($order_contents) {

        if (!is_array($order_contents)) {
            $order_contents = json_decode($order_contents, true);
        }

        if (!empty($order_contents[0])) {
            return $order_contents[0];
        } else {
            return false;
        }
    }

    public function finalizePayment($order_id, $token, $email_address=false, $customer_name=false, $shipping_info=false, $session_id=false, $total_price=false, $description=false, $finalize_url=false) {

      $this->startSession($session_id);
      // this just checks to see if we've started finalizing already. really
      // only an issue for embeds used on pages
      $working = $this->sessionGet('finalizing_payment');
      if ($working) {
         // already doing shit just check the order id
         if ($working == $order_id) {
            // doing it. just return the id here
            return $order_id;
         }
      }
      // nothing found? set the in-progress marker to the current id
      $this->sessionSet('finalizing_payment',$order_id);

        $order_details = $this->getOrder($order_id);
        $transaction_details = $this->getTransaction($order_details['transaction_id']);
        //error_log( print_r($transaction_details, true) );
        $connection_type = $this->getConnectionType($transaction_details->connection_id);
        $order_totals = $this->getOrderTotals($order_details['order_contents']);

        //TODO: since we haven't actually set the connection settings at this point, let's
        // get connection type settings so we can extract Seed classname
        $connection_settings = CASHSystem::getConnectionTypeSettings($connection_type);

        $seed_class = '\\CASHMusic\Seeds\\'.$connection_settings['seed'];

        // we're going to switch seeds by $connection_type, so check to make sure this class even exists
        if (!class_exists($seed_class)) {
            $this->setErrorMessage("Couldn't find payment type $connection_type.");
            return false;
        }


        // call the payment seed class
        $payment_seed = new $seed_class($order_details['user_id'],$transaction_details->connection_id);

        // if this was approved by the user, we need to compare some values to make sure everything matches up
        if ($payment_details = $payment_seed->doPayment($total_price, $description, $token, $email_address, $customer_name, $order_details['currency'])) {

            // okay, we've got the matching totals, so let's get the $user_id, y'all

            if ($payment_details['total'] >= $order_totals['price']) {

                if ($user_id = $this->getOrCreateUser($payment_details)) {
                    // marking order fulfillment for digital only, physical quantities, all that fun stuff
                    $is_fulfilled = $this->getFulfillmentStatus($order_details);


                    // takin' care of business
                    $this->editOrder(
                        $order_id, 		// order id
                        $is_fulfilled,	// fulfilled status
                        0,				// cancelled (boolean 0/1)
                        false,			// notes
                        false,	// country code
                        $user_id,		// user id
                        false,          // order contents
                        false,          // transaction id
                        false,          // physical
                        false,          // digital
                        false          // user id
                    );

                    //TODO: this is a temporary stopgap; we need to introduce JSON appending
                    if (!isset($shipping_info)) $shipping_info = $this->sessionGet('shipping_info');

                    if (is_array($shipping_info)) {
                        $payment_details = array_merge($payment_details, $shipping_info);
                    }

                    $this->editTransaction(
                        $order_details['transaction_id'], 		// order id
                        time(), 			// service timestamp
                        false,		// service transaction id
                        false,									// data sent
                        $payment_details,			// data received
                        1,										// successful (boolean 0/1)
                        $payment_details['total'],				// gross price
                        $payment_details['service_fee'],	// service fee
                        'complete'								// transaction status
                    );


                    // empty the cart at this point
                    $this->emptyCart($order_details['element_id'],$session_id);

                    // TODO: add code to order metadata so we can track opens, etc
                    $order_details['customer_details']['email_address'] = $payment_details['customer_email'];

                    $order_details['gross_price'] = $payment_details['total'];

                    try {
                        $this->sendOrderReceipt(false,$order_details,$finalize_url);
                    } catch (Exception $e) {
                        //TODO: what happens when order receipt not sent?
                    }


                    $this->unlockElement($order_details['element_id'], $order_details);

                    return $order_id;
                } else {
                    $this->setErrorMessage("Error in CommercePlant::finalizePayment. Couldn't find your account.");
                    return false;
                }

            } else {

                $this->setErrorMessage("Error in CommercePlant::finalizePayment. The order total and payment total don't match.");
                return false;
            }

        } else {
            $this->setErrorMessage("Error in CommercePlant::finalizePayment. There was an issue with this payment.");
            return false;
        }
    }

    /**
     * Find a user's id by their email, or create one
     * @param array $payer
     * @return int
     */

    protected function getOrCreateUser($payment_details) {
        // let's try to find this user id via email
        $user_request = new CASHRequest(
            array('cash_request_type' => 'people',
                'cash_action' => 'getuseridforaddress',
                'address' => $payment_details['customer_email'])
        );

        $user_id = $user_request->response['payload'];

        // no dice, so let's make them feel welcome and create an account
        if (!$user_id) {

            $user_request = new CASHRequest(
                array('cash_request_type' => 'system',
                    'cash_action' => 'addlogin',
                    'address' => $payment_details['customer_email'],
                    'password' => time(),
                    'username' => preg_replace('/\s+/', '', $payment_details['customer_first_name'] . $payment_details['customer_last_name']),
                    'is_admin' => 0,
                    'display_name' => $payment_details['customer_name'],
                    'first_name' => $payment_details['customer_first_name'],
                    'last_name' => $payment_details['customer_last_name'],
                    'address_country' => $payment_details['customer_countrycode'],
                    'data' => ['new_subscriber' => true]
                    )
            );

            $user_id = $user_request->response['payload'];
        }

        if (!$user_id) {
            //TODO: uh oh, something went wrong while trying to create a user, maybe?
            $this->setErrorMessage("Something went wrong while trying to create a new user.");
            return false;
        } else {
            return $user_id;
        }
    }

    /**
     * Mostly dealing with physical quantities, and order fulfillment status for digital
     * @param array $order_details
     * @return $is_fulfilled int
     */
    protected function getFulfillmentStatus(array $order_details) {
        // deal with physical quantities
        if ($order_details['physical'] == 1) {
           $debug_info = "ORDER CONTENTS\n" . json_encode($order_details['order_contents']) . "\n\n";
            $order_items = $order_details['order_contents'];
            $debug_info .= "DECODED TYPE\n" . gettype($order_items) . "\n\n";
            if (is_array($order_items)) {
                foreach ($order_items as $i) {
                    if ($i['available_units'] > 0 && $i['physical_fulfillment'] == 1) {
                        $item = $this->getItem($i['id']);
                        if ($i['variant']) {
                           $debug_info .= "INITIAL VARIANT\n" . $i['variant'] . "\n\n";
                           // IMPORTANT
                           // this decode then encode thing looks super dumb but it's
                           // actually critical. we're comparing against JSON strings
                           // encoded by json_encode. at this point we have the mostly
                           // decoded version of that. which means no escape characters.
                           // so we decode it fully to get an object then reencode it
                           // using json_encode and we have the same format our keys
                           // are stored in. pretty fucking dumb, huh?
                           $decoded = json_decode($i['variant']);
                           if ($decoded) {
                              $debug_info .= "DECODED SUCESSFULLY\n\n";
                              // we do this in an if statement so the old-style variants
                              // won't break on us
                              $i['variant'] = json_encode($decoded);
                           }
                           $debug_info .= "DECODE/RECODE VARIANT\n" . $i['variant'] . "\n\n";
                            $variant_id = 0;
                            $variant_qty = 0;
                            if ($item['variants']) {
                                foreach ($item['variants']['quantities'] as $q) {
                                   $debug_info .= "VARIANT OPTION: " . $q['key'] . "\n";
                                    if ($q['key'] == $i['variant']) {
                                        $variant_id = $q['id'];
                                        $variant_qty = $q['value'];
                                        break;
                                    }
                                }


                              $debug_info .= "\n\nVARIANT ID: " . $variant_id . "\n";
                                CASHSystem::errorLog($debug_info);

                                if ($variant_id) {
                                    $this->editItemVariant($variant_id, max($variant_qty-$i['qty'],0), $i['id']);
                                }
                            }
                        } else {
                            $available_units =
                                $this->editItem(
                                    $i['id'],
                                    false,
                                    false,
                                    false,
                                    false,
                                    false,
                                    max($item['available_units'] - $i['qty'],0)
                                );
                        }
                    }
                }
            }
        }

        $is_fulfilled = 0;

        // record all the details
        if ($order_details['physical'] == 0) {
            // if the order is 100% digital just mark it as fulfilled
            $is_fulfilled = 1;
        }

        return $is_fulfilled;

    }

    protected function sendOrderReceipt($id=false,$order_details=false,$finalize_url=false) {
        if (!$id && !$order_details) {
            return false;
        }
        if (!$order_details) {
            $order_details = $this->getOrder($id,true);
        }

        $order_totals = $this->getOrderTotals($order_details['order_contents']);
        try {
            $personalized_message = '';
            if ($order_details['element_id']) {
                $element_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'element',
                        'cash_action' => 'getelement',
                        'id' => $order_details['element_id']
                    )
                );

                if ($element_request->response['payload']) {
                    if (isset($element_request->response['payload']['options']['message_email'])) {
                        if ($element_request->response['payload']['options']['message_email']) {
                            $personalized_message = $element_request->response['payload']['options']['message_email'] . "\n\n";
                        }
                    }
                }
            }

            if ($order_details['digital']) {
                $addcode_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'element',
                        'cash_action' => 'addlockcode',
                        'element_id' => $order_details['element_id']
                    )
                );

                if (!$finalize_url) {
                    $finalize_url = CASHSystem::getCurrentURL();
                }

                return CASHSystem::sendEmail(
                    'Thank you for your order',
                    $order_details['user_id'],
                    $order_details['customer_details']['email_address'],
                    $personalized_message . "Your order is complete. Here are some details:\n\n**Order #" . $order_details['id'] . "**  \n"
                    . $order_totals['description'] . "  \n Total: " . CASHSystem::getCurrencySymbol($order_details['currency']) . number_format($order_details['gross_price'],2) . "\n\n"
                    . "\n\n" . '[View your receipt and any downloads](' . $finalize_url . '?cash_request_type=element&cash_action=redeemcode&code=' . $addcode_request->response['payload']
                    . '&element_id=' . $order_details['element_id'] . '&email=' . urlencode($order_details['customer_details']['email_address']) . '&order_id=' . $order_details['id'] . ')',
                    'Thank you.'
                );
            } else {
                return CASHSystem::sendEmail(
                    'Thank you for your order',
                    $order_details['user_id'],
                    $order_details['customer_details']['email_address'],
                    $personalized_message . "Your order is complete. Here are some details:\n\n**Order #" . $order_details['id'] . "**  \n"
                    . $order_totals['description'] . "  \n Total: " . CASHSystem::getCurrencySymbol($order_details['currency']) . number_format($order_details['gross_price'],2) . "\n\n",
                    'Thank you.'
                );
            }
        } catch (Exception $e) {
            // TODO: handle the case where an email can't be sent. maybe display the download
            //       code on-screen? that plus storing it with the order is probably enough
            return false;
        }
    }

    protected function cancelOrder($order_id,$user_id=false) {

        $order_details = $this->getOrder($order_id,true);
        $connection_type = $this->getConnectionType($order_details['connection_id']);

        // get connection type settings so we can extract Seed classname
        $connection_settings = CASHSystem::getConnectionTypeSettings($connection_type);
        $seed_class = '\\CASHMusic\Seeds\\'.$connection_settings['seed'];

        // we're going to switch seeds by $connection_type, so check to make sure this class even exists
        if (!class_exists($seed_class)) {
            $this->setErrorMessage("Couldn't find payment type $connection_type.");
            return false;
        }

        // if we send a user id, make sure that user matches the order
        if ($user_id) {
            if ($user_id != $order_details['user_id']) {
                return false;
            }
        }

        // call the payment seed class
        $payment_seed = new $seed_class($order_details['user_id'],$order_details['connection_id']);

        $refund_details = $payment_seed->refundPayment(
            $order_details['sale_id'],
            $order_details['total']
        );

        // check initial refund success
        if (!$refund_details) {
            $this->setErrorMessage("There was a problem issuing this refund.");
            return false;
        } else {

            // make sure the refund went through fully, return false if not
            $this->editOrder(
                $order_id,
                false,
                1,
                "Cancelled " . date("F j, Y, g:i a T") . "\n\n" . $order_details['notes']
            );

			// we need to edit the json in data_returned to reflect the new status; admin reads most transaction data from this json
			// instead of from the fields, for better future proofing

            $data_returned = json_decode($order_details);
            $data_returned['status'] = 'refunded';

            $this->editTransaction(
                $order_details['transaction_id'],
                false,
                false,
                false,
                $data_returned,
                false,
                false,
                false,
                'refunded'
            );

            return true;

            // NOTE:
            // we aren't restocking physical goods for a few reasons:
            // 1. cancellations should be less common than sales
            // 2. lack of inventory is a common reason to cancel, restocking makes it worse
            // 3. manually re-adding stock isn't hard
            // 4. if an order is a return of damaged goods, you won't restocking
            // 5. fuck it
        }
    }

    /**
     * Pulls analytics queries in a few different formats
     *
     * @return array
     */protected function getAnalytics($analtyics_type,$user_id,$date_low=false,$date_high=false) {
    //
    // left a commented-out switch so we can easily add more cases...
    //
    //switch (strtolower($analtyics_type)) {
    //	case 'transactions':
       if (!$date_low) $date_low = 201243600;
       if (!$date_high) $date_high = time();
       $result = $this->db->getData(
           'CommercePlant_getAnalytics_transactions',
           false,
           array(
               "user_id" => array(
                   "condition" => "=",
                   "value" => $user_id
               ),
               "date_low" => array(
                   "condition" => "=",
                   "value" => $date_low
               ),
               "date_high" => array(
                   "condition" => "=",
                   "value" => $date_high
               )
           )
       );

     $result = $this->db->table('commerce_transactions')
         ->select(['SUM(gross_price as total_gross', 'COUNT(id) AS total_transactions'])
         ->where('user_id', '=', $user_id)
         ->where('successful', '=', 1)
         ->whereBetween('creation_date', $date_low, $date_high)->get();

       if ($result) {
           return $result[0];
       } else {
           return $result;
       }
       //		break;
       //}
    }

    public function setErrorMessage($message) {
      if (defined('CASH_DEBUG')) {
         if (CASH_DEBUG) {
             error_log($message);
         }
      }
    }

    /**
     * Unlocks embed element for payment response
     * @param $element_id
     * @param array|bool $order_details
     */
    protected function unlockElement($element_id, $order_details=false) {
        if ($element_id) {
            // borrowed from ElementBase — use the same mechanism to unlock the element
            // that issued the order so we're not reliant on a page refresh thing for
            // stripe or for an element embedded via script on a page, etc.
            $lock_session = $this->sessionGet('unlocked_elements');
            if (is_array($lock_session)) {
                $key = array_search($element_id, $lock_session);
                if ($key === false) {
                    $lock_session[] = $element_id;
                    $this->sessionSet('unlocked_elements',$lock_session);
                }
            } else {
                $this->sessionSet('unlocked_elements',array($element_id));
            }

            if ($order_details) {
                // we're also going to set order details, which are used by the Store element
                $this->sessionSet('commerce-' . $element_id, $order_details);
            }
        }
    }

    public function getElementData($element_id, $user_id) {

        $element_request = new CASHRequest(
            array(
                'cash_request_type' => 'element',
                'cash_action' => 'getelement',
                'id' => $element_id,
                'user_id'   => $user_id
            )
        );

        if (!empty($element_request->response['payload'])) {

            return $element_request->response['payload'];
        } else {
            return false;
        }

    }

    /**
     * @param $user_id
     * @return object|bool
     */
    protected function getPaymentSeed($user_id, $connection_id=false)
    {

        $default_connections = self::getDefaultConnections($user_id);

        if (is_array($default_connections)) {
            $pp_default = (!empty($default_connections['paypal'])) ? $default_connections['paypal'] : false;
            $pp_micro = (!empty($default_connections['paypal_micro'])) ? $default_connections['paypal_micro'] : false;
            $stripe_default = (!empty($default_connections['stripe'])) ? $default_connections['stripe'] : false;

            $connection_id = $stripe_default;
        } else {
            return false; // no default PP shit set
        }



        //TODO: this should be dynamic
        $payment_seed = new StripeSeed($user_id, $connection_id);

        return $payment_seed;
    }

    public static function getDefaultConnections($user_id) {
        $settings_request = new CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'getsettings',
                'type' => 'payment_defaults',
                'user_id' => $user_id
            )
        );

        if (is_array($settings_request->response['payload'])) {

            $settings = $settings_request->response['payload'];
            $pp_default = (isset($settings['pp_default'])) ? $settings['pp_default'] : false;
            $pp_micro = (isset($settings['pp_micro'])) ? $settings['pp_micro'] : false;
            $stripe_default = (isset($settings['stripe_default'])) ? $settings['stripe_default'] : false;
        } else {
            return false; // no default shit set
        }

        return [
            'stripe' => $stripe_default,
            'paypal' => $pp_default,
            'paypal_micro' => $pp_micro
        ];
    }

    protected function manageWebhooks($customer_id,$action='transaction') {
        //TODO: we need to add automated webhooks adding
            // connection found, api instantiated
          /*
                    $mc = $api_connection['api'];
                    // webhooks
                    $api_credentials = CASHSystem::getAPICredentials();
                    $webhook_api_url = CASH_API_URL . '/verbose/commerce/addsubscriptiontransaction/origin/com.stripe/api_key/' . $api_credentials['api_key'];
                    if ($action == 'remove') {
                        return $mc->listWebhookDel($webhook_api_url);
                    } else {
                        return $mc->listWebhookAdd($webhook_api_url, $actions=null, $sources=null);
                        // TODO: What do we do when adding a webhook fails?
                        // TODO: Try multiple times?
                    }*/

        $data_request = new CASHRequest(null);
        $user_id = $data_request->sessionGet('cash_effective_user');

        $default_connections = CommercePlant::getDefaultConnections($user_id);

        $api_credentials = CASHSystem::getAPICredentials();

        // for now let's just add stripe
        if (is_array($default_connections)) {
            $pp_default = (!empty($default_connections['paypal'])) ? $default_connections['paypal'] : false;
            $pp_micro = (!empty($default_connections['paypal_micro'])) ? $default_connections['paypal_micro'] : false;
            $stripe_default = (!empty($default_connections['stripe'])) ? $default_connections['stripe'] : false;
        } else {
            return false; // no default PP shit set
        }

        // let's just add for now
        $seed_class = '\\CASHMusic\Seeds\\'."StripeSeed";
        if (!class_exists($seed_class)) {
            $this->setErrorMessage("1301 Couldn't find payment type $seed_class.");
            return false;
        }

        // call the payment seed class
        $payment_seed = new $seed_class($user_id,$stripe_default);


    }

    protected function processWebhook($origin,$type=false,$data=false) {

        // webhook is /api/verbose/commerce/processwebhook/origin/com.stripe
        if ($input = file_get_contents("php://input")) {

            if (is_json($input)) {
                $event = json_decode($input);
            } else {
                CASHSystem::errorLog("not valid json");
                return false; // not valid json?
            }

            //if ($event = \Stripe\Event::retrieve($event['id'])) {
                // if success or fail
                $payment_status = "failed";
                $plan_amount = 0;
                $status = "canceled";
                $plan_id = false;
                $customer_id = false;

                if ($event->type == "invoice.payment_succeeded" ||
                    $event->type == "invoice.payment_failed"
                ) {
                    // set data
                    $plan_id = $event->data->object->lines->data[0]->plan->id;
                    $plan_amount = (
                        (integer) $event->data->object->lines->data[0]->plan->amount *
                        (integer) $event->data->object->lines->data[0]->quantity
                    );
                    $customer_id = $event->data->object->lines->data[0]->id;
                }

                if ($event->type == "customer.subscription.deleted" || $event->type == "customer.subscription.updated") {
                    // set data
                    $plan_id = $event->data->object->plan->id;
                    $customer_id = $event->data->object->id;

                }
/*            } else {
                error_log("#### stripe event not retrieved");
                return false;
            }*/

            // we get the plan to override the user id we get via the webhook
            $plan = $this->getSubscriptionPlanBySku($plan_id);

            if (!$plan) return false; // something bogus about the plan id that's being passed here

            $user_id = $plan->user_id;

            // get customer info from commerce_subscriptions_members
            $customer = $this->getSubscriptionDetails($customer_id);

            if (!is_cash_model($customer)) return false; // this is not an existing customer

            // get customer email
            $user_request = new CASHRequest(
                array(
                    'cash_request_type' => 'people',
                    'cash_action' => 'getuser',
                    'user_id' => $customer->user_id
                )
            );

            if ($user_request->response['payload']) {
                $email_address = $user_request->response['payload']['email_address'];
            }

            if ($event->type == "invoice.payment_succeeded") {
                $paid_to_date = ((integer) $customer->total_paid_to_date + (integer) $this->centsToDollar($plan_amount));
                $payment_status = "success";
                $status = "active";
            } else {
                $paid_to_date = false;

                if ($event->type == "invoice.payment_failed") $payment_status = "failed";
                if ($event->type == "customer.subscription.deleted") $payment_status = "canceled";
                if ($event->type == "customer.subscription.updated") {
                    // this is a lapsed account
                    if (!empty($event->data->object->status == "unpaid")) {
                        $payment_status = "expired";

                        // send email
                        if (!empty($email_address) && !in_array($customer->status, ['canceled', 'comped', 'failed'])) {
                            if (!CASHSystem::sendEmail(
                                'Your CASH Music Family subscription has lapsed.',
                                $user_id,
                                $email_address,
                                "You can renew your subscription by visiting https://family.cashmusic.org/ and going through the signup process with the same email and credit card used originally.",
                                'Your CASH Music Family subscription has lapsed.'
                            )
                            ) {
                                return false;
                            }
                        }


                    }
                }
                $status = $payment_status;
            }

            if (!is_cash_model($customer)) return false;

            // we need to make sure this is a real event
            // for now let's just add stripe
            //$payment_seed = $this->getPaymentSeed($user_id);

            $default_connections = CommercePlant::getDefaultConnections($user_id);

            $this->addTransaction(
                $user_id,
                $default_connections['stripe'],
                "com.stripe",
                $event->created,
                $event->id,
                '',
                json_encode($event),
                1,
                $this->centsToDollar($plan_amount),
                0,
                $payment_status,
                'usd',
                'sub',
                $customer->id
            );

            // mark subscription member as active
            $this->updateSubscription(
                $customer->id,
                $status,
                $paid_to_date
            );

        } else {
            CASHSystem::errorLog("failed");
            return false;
        }

    }

    /**
     * @param $amount
     * @return string
     */
    public function centsToDollar($amount)
    {
        return number_format(($amount / 100), 2, '.', ' ');
    }

} // END class
?>
