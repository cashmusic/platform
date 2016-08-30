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
class CommercePlant extends PlantBase {


    public function __construct($request_type,$request) {
        $this->request_type = 'commerce';
        $this->routing_table = array(
            // alphabetical for ease of reading
            // first value  = target method to call
            // second value = allowed request methods (string or array of strings)
            'dothefixandstuff'         => array('setTimeStuff','direct'),

            'additem'                  => array('addItem','direct'),
            'additemvariants'          => array('addItemVariants','direct'),
            'addorder'                 => array('addOrder','direct'),
            'addtocart'				      => array('addToCart',array('get','post','direct','api_public')),
            'addtransaction'           => array('addTransaction','direct'),
            'cancelorder'			      => array('cancelOrder','direct'),
            'deleteitem'               => array('deleteItem','direct'),
            'deleteitemvariant'        => array('deleteItemVariant','direct'),
            'deleteitemvariants'       => array('deleteItemVariants','direct'),
            'editcartquantity'	      => array('editCartQuantity',array('get','post','direct','api_public')),
            'editcartshipping'	      => array('editCartShipping',array('get','post','direct','api_public')),
            'editfulfillmentorder'     => array('editFulfillmentOrder','direct'),
            'edititem'                 => array('editItem','direct'),
            'edititemvariant'   	      => array('editItemVariant','direct'),
            'editorder'                => array('editOrder','direct'),
            'edittransaction'          => array('editTransaction','direct'),
            'emailbuyersbyitem'	      => array('emailBuyersByItem','direct'),
            'emptycart'				      => array('emptyCart',array('get','post','direct','api_public')),
            'formatvariantname'        => array('formatVariantName','direct'),
            'getanalytics'             => array('getAnalytics','direct'),
            'getcart'				      => array('getCart','direct'),
            'getfulfillmentjobbytier'  => array('getFulfillmentJobByTier','direct'),
            'getfulfillmentorder'      => array('getFulfillmentOrder','direct'),
            'getitem'                  => array('getItem','direct'),
            'getitemvariants'          => array('getItemVariants','direct'),
            'getitemsforuser'          => array('getItemsForUser','direct'),
            'getorder'                 => array('getOrder','direct'),
            'getordersforuser'         => array('getOrdersForUser','direct'),
            'getordersbycustomer'      => array('getOrdersByCustomer','direct'),
            'getordersbyitem'		      => array('getOrdersByItem','direct'),
            'getordertotals' 		      => array('getOrderTotals','direct'),
            'gettransaction'           => array('getTransaction','direct'),
            'finalizepayment'          => array('finalizePayment',array('get','post','direct')),
            'initiatecheckout'         => array('initiateCheckout',array('get','post','direct','api_public')),
            'sendorderreceipt'	      => array('sendOrderReceipt','direct')
        );
        $this->plantPrep($request_type,$request);
    }

    protected function addItem(
        $user_id,
        $name,
        $description='',
        $sku='',
        $price=0,
        $flexible_price=0,
        $available_units=-1,
        $digital_fulfillment=0,
        $physical_fulfillment=0,
        $physical_weight=0,
        $physical_width=0,
        $physical_height=0,
        $physical_depth=0,
        $variable_pricing=0,
        $fulfillment_asset=0,
        $descriptive_asset=0,
        $shipping=''
    ) {
        if (!$fulfillment_asset) {
            $digital_fulfillment = false;
        } else {
            // if there's no descriptive asset we can try pulling the cover from the fulfillment asset
            if (empty($descriptive_asset)) {
                $request = new CASHRequest(
                    array(
                        'cash_request_type' => 'asset',
                        'cash_action' => 'getasset',
                        'id' => $fulfillment_asset
                    )
                );

                // we've got the request, we need to make sure the properties actually exist

                $fulfillment_asset_data = $request->response['payload'];
                if (is_array($fulfillment_asset_data['metadata'])
                    && isset($fulfillment_asset_data['metadata']['cover'])
                ) {
                    $descriptive_asset = $fulfillment_asset_data['metadata']['cover'];
                }
            }
        }
        $result = $this->db->setData(
            'items',
            array(
                'user_id' => $user_id,
                'name' => $name,
                'description' => $description,
                'sku' => $sku,
                'price' => $price,
                'shipping' => json_encode($shipping),
                'flexible_price' => (int)$flexible_price,
                'available_units' => $available_units,
                'digital_fulfillment' => (int)$digital_fulfillment,
                'physical_fulfillment' => (int)$physical_fulfillment,
                'physical_weight' => $physical_weight,
                'physical_width' => $physical_width,
                'physical_height' => $physical_height,
                'physical_depth' => $physical_depth,
                'variable_pricing' => (int)$variable_pricing,
                'fulfillment_asset' => $fulfillment_asset,
                'descriptive_asset' => $descriptive_asset
            )
        );
        return $result;
    }

    protected function addItemVariants(
        $item_id,
        $variants
    ) {

        $item_details = $this->getItem($item_id);
        if ($item_details) {
            $variant_ids = array();

            foreach ($variants as $attributes => $quantity) {

                $result = $this->db->setData(
                    'item_variants',
                    array(
                        'item_id' 		=> $item_id,
                        'user_id'		=> $item_details['user_id'],
                        'attributes' 	=> $attributes,
                        'quantity' 		=> $quantity,
                    )
                );

                if (!$result) {
                    return false;
                }

                $variant_ids[$attributes] = $result;
            }

            $this->updateItemQuantity($item_id);

            return $variant_ids;
        } else {
            return false;
        }
    }

    protected function getItem($id,$user_id=false,$with_variants=true) {
        $condition = array(
            "id" => array(
                "condition" => "=",
                "value" => $id
            )
        );
        if ($user_id) {
            $condition['user_id'] = array(
                "condition" => "=",
                "value" => $user_id
            );
        }
        $result = $this->db->getData(
            'items',
            '*',
            $condition
        );

        if ($result) {
            $item = $result[0];

            if ($with_variants) {
                $item['variants'] = $this->getItemVariants($id, $user_id);
            }

            $item['shipping'] = json_decode($item['shipping'],true);

            return $item;
        } else {
            return false;
        }
    }

    protected function getItemVariants($item_id, $exclude_empties=false, $user_id=false) {
        $condition = array(
            "item_id" => array(
                "condition" => "=",
                "value" => $item_id
            )
        );
        if ($user_id) {
            $condition['user_id'] = array(
                "condition" => "=",
                "value" => $user_id
            );
        }
        $result = $this->db->getData(
            'item_variants',
            '*',
            $condition
        );
        if ($result) {
            $variants = array(
                'attributes' => array(),
                'quantities' => array(),
            );
            $attributes = array();
            foreach ($result as $item) {
                // first try json_decode
                $attribute_array = json_decode($item['attributes'],true);
                if (!$attribute_array) {
                    // old style keys, so format them to match JSON
                    $attribute_array = array();
                    $attribute_keys = explode('+', $item['attributes']);
                    foreach ($attribute_keys as $part) {
                        list($key, $type) = array_pad(explode('->', $part, 2), 2, null);
                        // weird syntax to avoid warnings on: list($key, $type) = explode('->', $part);
                        $attribute_array[$key] = $type;
                    }
                }
                foreach ($attribute_array as $key => $type) {
                    // build the final attributes array
                    if (!isset($attributes[$key][$type])) {
                        $attributes[$key][$type] = 0;
                    }
                    $attributes[$key][$type] += $item['quantity'];
                }
                if (!($item['quantity'] < 1 && $exclude_empties)) {
                    $variants['quantities'][] = array(
                        'id' => $item['id'],
                        'key' => $item['attributes'],
                        'formatted_name' => $this->formatVariantName($item['attributes']),
                        'value' => $item['quantity']
                    );
                }
            }
            foreach ($attributes as $key => $values) {
                $items = array();
                foreach ($values as $type => $quantity) {
                    $items[] = array(
                        'key' => $type,
                        'value' => $quantity,
                    );
                }
                $variants['attributes'][] = array(
                    'key' => $key,
                    'items' => $items
                );
            }
            return $variants;
        } else {
            return false;
        }
    }

    protected function formatVariantName ($name) {
        $final_name = '';
        $name_decoded = json_decode($name,true);
        if ($name_decoded) {
            foreach ($name_decoded as $var => $val) {
                $final_name .= $var . ': ' . $val . ', ';
            }
            $final_name = rtrim($final_name,', ');
            return $final_name;
        } else {
            $totalmatches = preg_match_all("/([a-z]+)->/i", $name, $key_parts);
            if ($totalmatches) {
                $variant_keys = $key_parts[1];
                $variant_values = preg_split("/([a-z]+)->/i", $name, 0, PREG_SPLIT_NO_EMPTY);
                $count = count($variant_keys);
                $variant_descriptions = array();
                for($index = 0; $index < $count; $index++) {
                    $key = $variant_keys[$index];
                    $value = trim(str_replace('+', ' ', $variant_values[$index]));
                    $variant_descriptions[] = "$key: $value";
                }
                return implode(', ', $variant_descriptions);
            } else {
                return $name;
            }
        }
    }

    protected function editItem(
        $id,
        $name=false,
        $description=false,
        $sku=false,
        $price=false,
        $flexible_price=false,
        $available_units=false,
        $digital_fulfillment=false,
        $physical_fulfillment=false,
        $physical_weight=false,
        $physical_width=false,
        $physical_height=false,
        $physical_depth=false,
        $variable_pricing=false,
        $fulfillment_asset=false,
        $descriptive_asset=false,
        $user_id=false,
        $shipping=false
    ) {
        if ($fulfillment_asset === 0) {
            $digital_fulfillment = 0;
        }
        if ($fulfillment_asset > 0) {
            $digital_fulfillment = 1;
        }
        $final_edits = array_filter(
            array(
                'name' => $name,
                'description' => $description,
                'sku' => $sku,
                'price' => $price,
                'shipping' => $shipping,
                'flexible_price' => $flexible_price,
                'available_units' => $available_units,
                'digital_fulfillment' => $digital_fulfillment,
                'physical_fulfillment' => $physical_fulfillment,
                'physical_weight' => $physical_weight,
                'physical_width' => $physical_width,
                'physical_height' => $physical_height,
                'physical_depth' => $physical_depth,
                'variable_pricing' => $variable_pricing,
                'fulfillment_asset' => $fulfillment_asset,
                'descriptive_asset' => $descriptive_asset
            ),
            'CASHSystem::notExplicitFalse'
        );
        if (isset($final_edits['shipping'])) {
            $final_edits['shipping'] = json_encode($shipping);
        }
        $condition = array(
            "id" => array(
                "condition" => "=",
                "value" => $id
            )
        );
        if ($user_id) {
            $condition['user_id'] = array(
                "condition" => "=",
                "value" => $user_id
            );
        }
        $result = $this->db->setData(
            'items',
            $final_edits,
            $condition
        );
        return $result;
    }

    protected function editItemVariant($id, $quantity, $item_id, $user_id=false) {

        $condition = array(
            "id" => array(
                "condition" => "=",
                "value" => $id,
            )
        );

        if ($user_id) {
            $condition['user_id'] = array(
                "condition" => "=",
                "value" => $user_id
            );
        }

        $updates = array(
            'quantity' => $quantity
        );

        $result = $this->db->setData(
            'item_variants',
            $updates,
            $condition
        );

        if ($result) {
            $this->updateItemQuantity($item_id);
        }

        return $result;
    }

    protected function deleteItem($id,$user_id=false) {
        $condition = array(
            "id" => array(
                "condition" => "=",
                "value" => $id
            )
        );
        if ($user_id) {
            $condition['user_id'] = array(
                "condition" => "=",
                "value" => $user_id
            );
        }
        $result = $this->db->deleteData(
            'items',
            $condition
        );

        if (!$result) {
            return false;
        }
        $this->deleteItemVariants($id, $user_id);
        return $result;
    }

    protected function deleteItemVariant($id, $user_id=false) {

        $condition = array(
            "id" => array(
                "condition" => "=",
                "value" => $id
            )
        );

        if ($user_id) {
            $condition['user_id'] = array(
                "condition" => "=",
                "value" => $user_id
            );
        }

        $result = $this->db->deleteData(
            'item_variants',
            $condition
        );
        return $result;
    }

    protected function deleteItemVariants($item_id, $user_id=false) {

        $condition = array(
            "item_id" => array(
                "condition" => "=",
                "value" => $item_id
            )
        );

        if ($user_id) {
            $condition['user_id'] = array(
                "condition" => "=",
                "value" => $user_id
            );
        }

        $result = $this->db->deleteData(
            'item_variants',
            $condition
        );

        return $result;
    }

    protected function getItemsForUser($user_id,$with_variants=true) {
      $result = $this->db->getData(
          'CommercePlant_getItemsForUser',
          false,
          array(
               "user_id" => array(
                   "condition" => "=",
                   "value" => $user_id
               )
          )
      );

        if ($with_variants) {
            $length = count($result);

            for ($index = 0; $index < $length; $index++) {
                $result[$index]['variants'] = $this->getItemVariants($result[$index]['id'], false, $user_id);
                $result[$index]['shipping'] = json_decode($result[$index]['shipping'],true);
            }
        }

        return $result;
    }

    protected function emailBuyersByItem($user_id,$connection_id,$item_id,$subject,$message,$include_download=false) {

        if (CASH_DEBUG) {
            error_log(
                'Requested CommercePlant->emailBuyersByItem with: '
                .'$user_id='. (string)$user_id
                .',$item_id='. (string)$item_id
                .',$connection_id='. (string)$connection_id
                .',$subject='. (string)$subject
                .',$message='. (string)$message
                .',$include_download='. (string)$include_download
            );
        }

        $item_details = $this->getItem($item_id);

        if ($item_details['user_id'] == $user_id) {
            $merge_vars = null;
            $global_merge_vars = array(
                array(
                    'name' => 'itemname',
                    'content' => $item_details['name']
                ),
                array(
                    'name' => 'itemdescription',
                    'content' => $item_details['description']
                )
            );

            //TODO: move these to the outer solar system in their own template

            $recipients = array();
            $tmp_recipients = array();
            $all_orders = $this->getOrdersByItem($user_id,$item_id);

            // if there are no orders, let's cheese it
            //TODO: no error being displayed
            if (empty($all_orders)) {
                return false;
            }

            foreach ($all_orders as $order) {
                $tmp_recipients[] = $order['customer_email'];
            }
            $tmp_recipients = array_unique($tmp_recipients);

            foreach ($tmp_recipients as $email) {
                $recipients[] = array(
                    'email' => $email
                );
            }

            if (count($recipients)) {

                $html_message = CASHSystem::parseMarkdown($message);

                if ($include_download) {
                    $asset_request = new CASHRequest(
                        array(
                            'cash_request_type' => 'asset',
                            'cash_action' => 'getasset',
                            'id' => $item_details['fulfillment_asset']
                        )
                    );
                    if ($asset_request->response['payload']) {
                        $unlock_suffix = 1;
                        $all_assets = array();
                        if ($asset_request->response['payload']['type'] == 'file') {
                            $message .= "\n\n" . 'Download *|ITEMNAME|*: at '.CASH_PUBLIC_URL.'/download/?code=*|UNLOCKCODE1|*';
                            $html_message .= "\n\n" . '<p><b><a href="'.CASH_PUBLIC_URL.'/download/?code=*|UNLOCKCODE1|*">Download *|ITEMNAME|*</a></b></p>';
                            $all_assets[] = array(
                                'id' => $item_details['fulfillment_asset'],
                                'name' => $asset_request->response['payload']['title']
                            );

                        } else {
                            $message .= "\n\n" . '*|ITEMNAME|*:' . "\n\n";
                            $html_message .= "\n\n" . '<p><b>*|ITEMNAME|*:</b></p>';
                            $fulfillment_request = new CASHRequest(
                                array(
                                    'cash_request_type' => 'asset',
                                    'cash_action' => 'getfulfillmentassets',
                                    'asset_details' => $asset_request->response['payload']
                                )
                            );
                            if ($fulfillment_request->response['payload']) {
                                foreach ($fulfillment_request->response['payload'] as $asset) {
                                    $all_assets[] = array(
                                        'id' => $asset['id'],
                                        'name' => $asset['title']
                                    );
                                    $message .= "\n\n" . 'Download *|ASSETNAME'.$unlock_suffix.'|* at '.CASH_PUBLIC_URL.'/download/?code=*|UNLOCKCODE'.$unlock_suffix.'|*';
                                    $html_message .= "\n\n" . '<p><b><a href="'.CASH_PUBLIC_URL.'/download/?code=*|UNLOCKCODE'.$unlock_suffix.'|*">Download *|ASSETNAME'.$unlock_suffix.'|*</a></b></p>';
                                    $unlock_suffix++;
                                }
                            }
                        }
                        $merge_vars = array();
                        $all_vars = array();
                        $unlock_suffix = 1;
                        $success = true;


                        //TODO: really we want to do this in one shot with the API

                        foreach ($recipients as $recipient) {

                            foreach ($all_assets as $asset) {
                                $addcode_request = new CASHRequest(
                                    array(
                                        'cash_request_type' => 'asset',
                                        'cash_action' => 'addlockcode',
                                        'asset_id' => $asset['id']
                                    )
                                );
                                $all_vars[] = array(
                                    'name' => 'assetname'.$unlock_suffix,
                                    'content' => $asset['name']
                                );
                                $all_vars[] = array(
                                    'name' => 'unlockcode'.$unlock_suffix,
                                    'content' => $addcode_request->response['payload']
                                );

                                // replace asset name
                                $recipient_message = str_replace
                                (
                                    '*|ASSETNAME'.$unlock_suffix.'|*',
                                    $asset['name'],
                                    $html_message
                                );

                                $recipient_message = str_replace
                                (
                                    '*|ITEMNAME|*',
                                    $global_merge_vars[0]['content'],
                                    $recipient_message
                                );



                                // replace unlock code
                                $recipient_message = str_replace
                                (
                                    '*|UNLOCKCODE'.$unlock_suffix.'|*',
                                    $addcode_request->response['payload'],
                                    $recipient_message
                                );

                                $unlock_suffix++;
                            }
                            if ($addcode_request->response['payload']) {
                                $merge_vars[] = array(
                                    'rcpt' => $recipient['email'],
                                    'vars' => $all_vars
                                );
                            }

                            $all_vars = array();
                            $unlock_suffix = 1;

                        }
                    }
                }

                // by the power of grayskull
                CASHSystem::sendMassEmail(
                    $user_id,
                    $subject,
                    $recipients,
                    $html_message,
                    $subject,
                    $global_merge_vars,
                    $merge_vars,
                    false,
                    true
                );

                if (!$success) return false;

                return true;
            }
        } else {
            return false;
        }
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
            $final_order_contents = json_encode($order_contents);
            $result = $this->db->setData(
                'orders',
                array(
                    'user_id' => $user_id,
                    'customer_user_id' => $customer_user_id,
                    'transaction_id' => $transaction_id,
                    'order_contents' => $final_order_contents,
                    'fulfilled' => $fulfilled,
                    'canceled' => $canceled,
                    'physical' => $physical,
                    'digital' => $digital,
                    'notes' => $notes,
                    'country_code' => $country_code,
                    'currency' => $currency,
                    'element_id' => $element_id,
                    'cash_session_id' => $cash_session_id,
                    'data' => json_encode($data)
                )
            );
            return $result;
        } else {
            return false;
        }
    }

    protected function getOrder($id,$deep=false,$user_id=false) {
        if ($deep) {
            $result = $this->db->getData(
                'CommercePlant_getOrder_deep',
                false,
                array(
                    "id" => array(
                        "condition" => "=",
                        "value" => $id
                    )
                )
            );

            if ($result) {
                if ($user_id) {
                    if ($result[0]['user_id'] != $user_id) {
                        return false;
                    }
                }

                if (!empty($result[0]['data'])) {
                    $result[0]['data'] = json_decode($result[0]['data']);
                }
                $result[0]['order_totals'] = $this->getOrderTotals($result[0]['order_contents']);
                $result[0]['order_description'] = $result[0]['order_totals']['description'];
                $transaction_data = $this->parseTransactionData($result[0]['data_returned'],$result[0]['data_sent']);

                if (is_array($transaction_data)) {
                    $result[0] = array_merge($result[0],$transaction_data);
                }

                $user_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'people',
                        'cash_action' => 'getuser',
                        'user_id' => $result[0]['customer_user_id']
                    )
                );
                $result[0]['customer_details'] = $user_request->response['payload'];
            }
        } else {
            $condition = array(
                "id" => array(
                    "condition" => "=",
                    "value" => $id
                )
            );
            if ($user_id) {
                $condition['user_id'] = array(
                    "condition" => "=",
                    "value" => $user_id
                );
            }
            $result = $this->db->getData(
                'orders',
                '*',
                $condition
            );

            if (!empty($result[0]['data'])) {
                $result[0]['data'] = json_decode($result[0]['data']);
            }
        }

        if ($result) {
            return $result[0];
        } else {
            return false;
        }
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
            'CASHSystem::notExplicitFalse'
        );
        if (isset($final_edits['order_contents'])) {
            $final_edits['order_contents'] = json_encode($order_contents);
        }
        if (isset($final_edits['data'])) {
            $final_edits['data'] = json_encode($data);
        }
        $condition = array(
            "id" => array(
                "condition" => "=",
                "value" => $id
            )
        );
        if ($user_id) {
            $condition['user_id'] = array(
                "condition" => "=",
                "value" => $user_id
            );
        }
        $result = $this->db->setData(
            'orders',
            $final_edits,
            $condition
        );
        return $result;
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

    protected function getOrdersForUser($user_id,$include_abandoned=false,$max_returned=false,$since_date=0,$unfulfilled_only=0,$deep=false,$skip=0) {
        if ($max_returned) {
            $limit = $skip . ', ' . $max_returned;
        } else {
            $limit = false;
        }
        if ($deep) {
            $result = $this->db->getData(
                'CommercePlant_getOrders_deep',
                false,
                array(
                    "user_id" => array(
                        "condition" => "=",
                        "value" => $user_id
                    ),
                    "unfulfilled_only" => array(
                        "condition" => "=",
                        "value" => $unfulfilled_only
                    ),
                    "since_date" => array(
                        "condition" => ">",
                        "value" => $since_date
                    )
                ),
                $limit
            );
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
        } else {
            $conditions = array(
                "user_id" => array(
                    "condition" => "=",
                    "value" => $user_id
                ),
                "creation_date" => array(
                    "condition" => ">",
                    "value" => $since_date
                ),
                "customer_user_id" => array(
                    "condition" => ">",
                    "value" => 0
                )
            );
            if ($unfulfilled_only) {
                $conditions['fulfilled'] = array(
                    "condition" => "=",
                    "value" => 0
                );
            }
            if (!$include_abandoned) {
                $conditions['modification_date'] = array(
                    "condition" => ">",
                    "value" => 0
                );
            }
            $result = $this->db->getData(
                'orders',
                '*',
                $conditions,
                $limit,
                'id DESC'
            );
        }
        return $result;
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

        $result = $this->db->getData(
            'orders',
            '*',
            array(
                "user_id" => array(
                    "condition" => "=",
                    "value" => $user_id
                ),
                "customer_user_id" => array(
                    "condition" => "=",
                    "value" => $customer_id
                ),
                "modification_date" => array(
                    "condition" => ">",
                    "value" => 0
                )
            )
        );
        return $result;
    }

    protected function getOrdersByItem($user_id,$item_id,$max_returned=false,$skip=0) {
        if ($max_returned) {
            $limit = $skip . ', ' . $max_returned;
        } else {
            $limit = false;
        }
        $result = $this->db->getData(
            'CommercePlant_getOrders_deep',
            false,
            array(
                "user_id" => array(
                    "condition" => "=",
                    "value" => $user_id
                ),
                "contains_item" => array(
                    "condition" => "=",
                    "value" => '%"id":"' . $item_id . '"%'
                )
            ),
            $limit
        );
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
        $currency='USD'
    ) {
        $result = $this->db->setData(
            'transactions',
            array(
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
                'status' => $status
            )
        );
        return $result;
    }

    protected function getTransaction($id,$user_id=false) {
        $condition = array(
            "id" => array(
                "condition" => "=",
                "value" => $id
            )
        );
        if ($user_id) {
            $condition['user_id'] = array(
                "condition" => "=",
                "value" => $user_id
            );
        }
        $result = $this->db->getData(
            'transactions',
            '*',
            $condition
        );

         if ($result) {
            if (!empty($result[0]['data_sent'])) {
               $result[0]['data_sent'] = json_decode($result[0]['data_sent'], true);
            }
            if (!empty($result[0]['data_returned'])) {
               $result[0]['data_returned'] = json_decode($result[0]['data_returned'], true);
            }
            return $result[0];
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
            'CASHSystem::notExplicitFalse'
        );
        if (isset($final_edits['data_sent'])) {
            $final_edits['data_sent'] = json_encode($data_sent);
        }
        if (isset($final_edits['data_returned'])) {
            $final_edits['data_returned'] = json_encode($data_returned);
        }

        $result = $this->db->setData(
            'transactions',
            $final_edits,
            array(
                'id' => array(
                    'condition' => '=',
                    'value' => $id
                )
            )
        );

        return $result;
    }

    protected function updateItemQuantity(
        $id
    ) {

        $result = $this->db->getData(
            'CommercePlant_getTotalItemVariantsQuantity',
            false,
            array(
                "item_id" => array(
                    "condition" => "=",
                    "value" => $id
                )
            )
        );

        if (!$result) {
            return false;
        }

        $updates = array(
            'available_units' => $result[0]['total_quantity']
        );

        $condition = array(
            "id" => array(
                "condition" => "=",
                "value" => $id
            )
        );

        $result = $this->db->setData(
            'items',
            $updates,
            $condition
        );

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

      if (CASH_DEBUG) {
         error_log(
            'Called CommercePlant::initiateCheckout with: '
            . '$element_id='      . (string)$element_id
            . ', $shipping_info=' . (string)$shipping_info
            . ', $paypal='        . (string)$paypal
            . ', $stripe='        . (string)$stripe
            . ', $origin='        . (string)$origin
            . ', $email_address=' . (string)$email_address
            . ', $customer_name=' . (string)$customer_name
            . ', $session_id='    . (string)$session_id
            . ', $geo='    . (string)$geo
         );
      }

        //TODO: store last seen top URL
        //      or maybe make the API accept GET params? does it already? who can know?
        //$r = new CASHRequest();
        $this->startSession($session_id);
        if (!$element_id) {
            return false;
        } else {
            $is_physical = 0;
            $is_digital = 0;

            $element_request = new CASHRequest(
              array(
                  'cash_request_type' => 'element',
                  'cash_action' => 'getelement',
                  'id' => $element_id
              )
            );
            $user_id = $element_request->response['payload']['user_id'];
            $settings_request = new CASHRequest(
              array(
                  'cash_request_type' => 'system',
                  'cash_action' => 'getsettings',
                  'type' => 'payment_defaults',
                  'user_id' => $user_id
              )
            );
            if (is_array($settings_request->response['payload'])) {
              $pp_default = (isset($settings_request->response['payload']['pp_default'])) ? $settings_request->response['payload']['pp_default'] : false;
              $pp_micro = (isset($settings_request->response['payload']['pp_micro'])) ? $settings_request->response['payload']['pp_micro'] : false;
              $stripe_default = (isset($settings_request->response['payload']['stripe_default'])) ? $settings_request->response['payload']['stripe_default'] : false;
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

            if (CASH_DEBUG) {
               error_log(
                  'In CommercePlant::initiateCheckout found: '
                  . '$total_price=' . (string)$total_price
                  . ', $subtotal='  . (string)$subtotal
                  . ', $shipping='  . (string)$shipping
               );
            }

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
              if (CASH_DEBUG) {
                 error_log(
                    'In CommercePlant::initiateCheckout using Stripe.'
                 );
              }
            }

            if ($paypal != false) {
              $seed_class = "PaypalSeed";
              //TODO: this connection stuff is hard-coded for paypal, but does the default/micro switch well
              if (($subtotal+$shipping < 12) && $pp_micro) {
                  $connection_id = $pp_micro;
              } else {
                  $connection_id = $pp_default;
              }
              if (CASH_DEBUG) {
                 error_log(
                    'In CommercePlant::initiateCheckout using Paypal.'
                 );
              }
            }

            $currency = $this->getCurrencyForUser($user_id);

            // merge all this stuff into $data for storage
            $shipping_info = json_decode($shipping_info, true);
            $data = array("geo" => $geo);

            if ($shipping_info) {
                $data = array_merge($shipping_info, $data);
            }


            $transaction_id = $this->addTransaction(
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
                $transaction_id,
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

                $transaction_details = $this->getTransaction($order_details['transaction_id']);

                //TODO: we'll need to figure out a way to get the connection_id for whatever payment method was chosen, in order to switch on the fly
                $connection_type = $this->getConnectionType($transaction_details['connection_id']);
                $order_totals = $this->getOrderTotals($order_details['order_contents']);

                // ascertain whether or not this seed requires a redirect, else let's cheese it right to the charge
                // we're going to switch seeds by $connection_type, so check to make sure this class even exists
                if (!class_exists($seed_class)) {
                    $this->setErrorMessage("1301 Couldn't find payment type $seed_class.");
                    return false;
                }

                // call the payment seed class
                $payment_seed = new $seed_class($user_id,$transaction_details['connection_id']);

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

                    if (CASH_DEBUG) {
                       error_log(
                          'In CommercePlant::initiateCheckout redirecting to ' . $return_url
                       );
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
                        $transaction_id                         # for adding data sent
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

    protected function getOrderTotals($order_contents) {
        $contents = json_decode($order_contents,true);
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
        $contents = json_decode($order_contents, true);

        if (!empty($contents[0])) {
            return $contents[0];
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


      if (CASH_DEBUG) {
         error_log(
            'Called CommercePlant::finalizePayment with: '
            . '$order_id='               . (string)$order_id
            . ', $token='                . (string)$token
            . ', $email_address='        . (string)$email_address
            . ', $customer_name='        . (string)$customer_name
            . ', $shipping_info='        . (string)$shipping_info
            . ', $session_id='           . (string)$session_id
            . ', $total_price='          . (string)$total_price
            . ', $description='          . (string)$description
         );
      }


        $order_details = $this->getOrder($order_id);
        $transaction_details = $this->getTransaction($order_details['transaction_id']);
        //error_log( print_r($transaction_details, true) );
        $connection_type = $this->getConnectionType($transaction_details['connection_id']);
        $order_totals = $this->getOrderTotals($order_details['order_contents']);

        //TODO: since we haven't actually set the connection settings at this point, let's
        // get connection type settings so we can extract Seed classname
        $connection_settings = CASHSystem::getConnectionTypeSettings($connection_type);

        $seed_class = $connection_settings['seed'];

        if (CASH_DEBUG) {
           error_log(
             'In CommercePlant::finalizePayment using seed class ' . $seed_class
           );
        }

        // we're going to switch seeds by $connection_type, so check to make sure this class even exists
        if (!class_exists($seed_class)) {
            $this->setErrorMessage("Couldn't find payment type $connection_type.");
            return false;
        }


        // call the payment seed class
        $payment_seed = new $seed_class($order_details['user_id'],$transaction_details['connection_id']);

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
                    $shipping_info = $this->sessionGet('shipping_info');

                    $payment_details = array_merge($payment_details, $shipping_info);

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

                     if (CASH_DEBUG) {
                        error_log(
                          'In CommercePlant::finalizePayment. Success! Order number ' . $order_id
                        );
                     }

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
                    'address_country' => $payment_details['customer_countrycode'])
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
           $debug_info = "ORDER CONTENTS\n" . $order_details['order_contents'] . "\n\n";
            $order_items = json_decode( $order_details['order_contents'],true);
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
                              // error_log($debug_info);

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
        $seed_class = $connection_settings['seed'];

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
       if ($result) {
           return $result[0];
       } else {
           return $result;
       }
       //		break;
       //}
    }

    public function setErrorMessage($message) {
      if (CASH_DEBUG) {
         error_log($message);
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
        $notes=false
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
            'CASHSystem::notExplicitFalse'
        );
        if (isset($final_edits['order_data'])) {
            $final_edits['order_data'] = json_encode($data_sent);
        }

        $result = $this->db->setData(
            'external_fulfillment_orders',
            $final_edits,
            array(
                'id' => array(
                    'condition' => '=',
                    'value' => $id
                )
            )
        );

        return $result;
    }

    protected function getFulfillmentOrder($id,$user_id=false) {
        $condition = array(
            "id" => array(
                "condition" => "=",
                "value" => $id
            )
        );
        if ($user_id) {
            $condition['user_id'] = array(
                "condition" => "=",
                "value" => $user_id
            );
        }
        $result = $this->db->getData(
            'external_fulfillment_orders',
            '*',
            $condition
        );

        if ($result) {
            return $result[0];
        } else {
            return false;
        }
    }

    protected function getFulfillmentJobByTier($tier_id) {
         $result = $this->db->getData(
              'external_fulfillment_tiers',
              '*',
              array(
                  "id" => array(
                      "condition" => "=",
                      "value" => $tier_id
                  )
              )
         );
         if ($result) {
            $tier = $result[0];
            $new_result = $this->db->getData(
                 'external_fulfillment_jobs',
                 '*',
                 array(
                     "id" => array(
                         "condition" => "=",
                         "value" => $tier['fulfillment_job_id']
                     )
                 )
            );
            if ($new_result) {
               return $new_result[0];
            }
         }
    }

    protected function setTimeStuff() {
      /* IMPORTANT TO-DO
       *
       * This function is FOR TESTING ONLY! Do not allow it to run in production. Delete it
       * from the repo entirely when we finish stress testing.
       *
       */
      $uk_postalcodes = array(
      	"n3 1sn","L19 3PZ","SW16 6TD","SE20 7NE","BN1 4GH","HG3 1RY","PL5 1AH","CM24 8UX","GL51 9SN","E17 4GE",
      	"TW1 4HB","AL5 4LH","BN1 4EW","W4 3TE","EH9 2BN","GU1 2UP","RH15 8AT","HP4 3EQ","WS1 2QD","DE4 2TS","dh5 8et",
      	"LS123SF","SN1 3LA","E17 9ET","EC1V 7DF","N6 5DZ","ST5 3NB","EN8 0FF","NW3 4JG","TW11 8HH","nr3 1ew","sw66nz",
      	"GL2 4NE","SE20 7HU","S2 3AX","CF3 4AW","NG24 1SE","M20 2QU","BN2 4AL","G44 4TD","E5 0LY","GU7 1QR","N2 0JE",
      	"WS7 1JN","CV1 2DF","YO30 6AX","SE1 0UP","NE9 6YP","NG5 8QT","SE1 5RU","SE11 4JJ","KT14 6AL","SO21 2HD",
      	"cm776gx","GU34 2AX","SW8 1XJ","N4 2LN","BT67 0LY","ME1 2HS","BS23 2UJ","AL1 4EJ","SG2 8AU","Yo12 7bq","KT7 0BQ",
      	"S21 4AX","N7 9RW","BN3 7EJ","SE5 8QF","G4 9AD","pl6 5qn","G32 9NB","SO45 5TA","w1b 5an","TW1 1DG","TN5 6BD",
      	"SW16 4JQ","OX2 9HL","ct19 4hj","PL9 7RU","HR4 0DX","SW9 7LN","SW3 6EX","RM12 6AJ","DA9 9FB","HP160QF","S71 2JS",
      	"LE10 1DR","S81 7LT","GL5 4EN","SE12 0BY","EH6 5FB","NE30 4BX","CB25 0EJ","Pe10 0rj","HX3 9QR","SR6 7HE",
      	"NE61 2SG","SK11 0JX","IP3 0PB","EH10 5PW","NG7 5PX","RG21 4EB","B63 1EA","RG14 2NA","SE5 8DD","N7 6FA",
      	"KT4 7RT","CR5 1RZ","CR8 2QA","BN21 2NX","BA4 5UD","CF72 9BD","BS2 8AR","OX7 3TE","eh14 2nr","BS5 7AX","HP2 5AD",
      	"CB1 7TZ","PO5 2HA","SW16 5PP","MK42 8PD","BS3 1PL","CW12 2EH","eh30 9xr","NW5 1DA","TN24 9NS","NR14 6SD",
      	"BN3 5QJ","NW10 5BJ","WA4 1UY","MK40 2NX","AB15 6AR","NW1 7DZ","SO19 9BS","HD47JX","WC1H 0AJ","st9 9hx",
      	"SL1 3JF","Tn24 8nd","DA14 4DE","NG3 2AY","DD8 1JU","NE33 2TG","SW179QG","RH8 0EP","W13 9RW","BA11 4LA","EH3 7RN",
      	"GU7 1GX","RG24 8XS","B32 2HL","NW5 4AX","SS3 9LE","TA6 7RB","AB10 6XJ","E2 8NG","L3 4FH","MK416ED","GU51 4QX",
      	"BL47PQ","CB23 6ES","E1W 2RX","HD8 8BB","W10 5 JJ","CR0 4WQ","PO4 8AB","CF11 9EN","NG3 3AT","N14 5AT","SK9 1NX",
      	"BR7 6SJ","NE6 5HS","EN6 2QE","BN42 4NP","RM155BA","EX39 4EU","AL4 0AQ","M4 5JJ","NP26 3UZ","RG24 9EY","MK10 7EE",
      	"NW8 8JP","CB24 9XP","G61 3QD","TR18 2BA","WD7 7EB","W10 6DD","M41 0ZH","SE7 7NX","CM7 2QS","se15 4ur","EC2M 4PT",
      	"E11 4DR","SK4 5HF","SS2 4DD","DE45 1JL","SS9 1LA","KY12 7SX","W6 9TS","CF14 6SG","PR8 5DZ","HP21 7LW","AL3 8HR",
      	"RH4 2DD","DA3 7JG","CH4 8TX","ST18 9AD","E2 6EX","BS1 5TX","AB125YP","MK18 3DQ","cf23 5bt","Ex7 9ps","NW10 5XB",
      	"Ol42pz","LL12 0ET","SE15 5SZ","B93 8NU","W3 7SX","SW17 9NE","S10 2FS","M2 5GB","G4 0TQ","PO22 0DQ","GU14 9JT",
      	"EX32 7AJ","WS15 2UP","sk8 1pp","BN11 3DR","L8 0TL","NE11 9QB","BN3 8BY","WS2 7BF","YO24 1GY","OX17 1LX","SO22 6RE",
      	"SE25 6NY","S10 3BH","GU15 1RW","RH10 5AR","BN2 1JN","EH6 6RB","CM14 4SD","CO3 3JE","ka23 9lx","IP2 8LH","S5 6WJ",
      	"LS12 1LB","YO16DP","NG1 1QE","KY16 9SX","SE22 9JZ","BR1 5BE","HP3 9SB","CB10 2AG","N2 0AS","WF1 3SB","HG2 9BT",
      	"IP16 4GU","CR0 8ED","m218de","NP16 6JR","RH5 4AW","KT13 8XT","BN14 7DP","SE10 8EN","M21 0ZJ","KT12UA","N16 5DG",
      	"SW2 5LA","BS6 6XB","CV32 4JG","NN5 6NR","SG1 5DB","OX14 5QD","MK40 3TG","SL6 4PP","BR51NA","B13 8LW","SW16 5JA",
      	"PE19 2BW","WF8 3FB","BS13 7RY","ls279ph","SG2 8ND","LS8 3TH","EH15 1QW","N8 0NG","SW11 6PE","NW3 1DE","EC2Y 8BN",
      	"CR7 8LU","SW15 5HY","LE1 6YF","SW9 9LU","SG8 5LW","SG5 4EY","E13 8ND","IG8 7DD","WN8 7TW","E2 9AG","NG24 2TN",
      	"W11 2RP","SS129SD","E5 0DP","CB1 9SA","se4 2dt","RM11 1LU","OX4 3UJ","OX11 8HB","GY5 7TJ","CO7 0PS","DY10 1UR",
      	"dd4 8qg","DY13 8PW","S43 3PY","WD17 4PY","BN8 6JL","BA1 3BS","KT18 7LX","DT2 7AZ","RG1 7YW ","b73 5xp","AB21 9BU",
      	"G61 1BB","BA11 4LF","NW3 4SB","BH19 2PN","BA3 2DN","DA6 7HQ","LL19 8SD","OX14 3YA","TW12 1NT","RG23 8JN",
      	"tw20 9eb","OX12 8DL","ss142pr","E14 3SW","SE11 5AY","B29 7NJ","E2 7JL","NP8 1DJ","GU3 1LH","SM4 4HX","SO40 7GE",
      	"SE6 3QF","ha2 0us","LE1 6BU","W1K 7DA","MK16 0AR","BD17 6AL","sk4 2ht","EH51 0JJ","NN15 7AX","G69 9HD","DA5 2JN",
      	"BN1 4NW","N16 0NS","GL15 5NZ","hp11 1bt","SS1 2YS","N6 5PZ","IM9 2AW","M14 5JR","W1U 4BY","KY12 9AT","HP2 4LY",
      	"FK5 3JP","RG6 1NS","WC1N 1EX","SW20 0ER","BS4 1NL","MK10 7AY","Cm1 7dn","SW2 5AF","EH9 1PL","HP23 4BJ","UB6 7QR",
      	"SN14 6EF","W11 1HG","N7 7JQ","SW11 3YR","N16 8RE","CF14 6EA","CB3 0HX","Bh13 6an","B14 7ER","BD5 8QY","WV10 9BH",
      	"SE14 5NP","eh7 4nl","BS6 5TN","N2 8NU","IP28 7LL","DE23 6NQ","G12 0ne","DE21 5LF","EX4 6AW","n11bp","SE10 8UA",
      	"M33 3AT","NW6 4TD","NN1 4BN","W2 6AN","PO22 9HW","NR32 4DN","NG4 2GL","LU4 8BA","DE23 1BY","EC3R 7NE","NG24 1SD",
      	"CV7 7EX","LE3 2EB","M415ND","SG5 4HF","L36 2NU","BR5 2SH","HP18 9BJ","SE15 4BZ","B50 4EU","PE2 8EF","RG6 7YB",
      	"BS7 0RG","SW16 4SU","RG30 6XB","HU5 2AD","BN3 2FB","MK12 5FD","UB3 4FA","WA8 9PE","HA2 9QG","TR26 2RZ","Td6 0dw",
      	"SE17 3HW","DE13 0RB","SE15 4PT","BL2 1LS","BA2 5PP","DE4 3QU","SN15 3AN","NG7 6LS","NW6 6AG","CM8 1RT","HP16 9EW",
      	"EH9 3AU","S73 8ll","tw26hj","NN1 4DX","W1F 7LP","ME16 8LL","EX1 1DU","CM23 4GJ","RG10 9LB","SN8 3RB","LS6 1AZ",
      	"SE1 9QE","W1F 9NB","HG1 4RF","G43 1DE","BA1 5QF","Bs15hl","N2 8AS","SE16 3UP","BS34 8NJ","TN15 7PE","SO51 5RA",
      	"BS4 2EU","CB4 3SD","Dy11 6pg","kt70ln","EH7 4BU","BS6 6AW","Ex39 4rs","LE9 9FH","NW1 9LR","HA5 2LP","G43 2ED",
      	"EC1M 4DE","SE5 0HB","SW16 1PS","HG2 9HP","CF52YB","CR4 1AE","SW20 8EG","TN4 0AN","SE23 2HW","SY21 0RN","SE26 6BQ",
      	"HP15 6NN","B16 8TP","WD7 8NY","NR27 0DN","S6 1JR","bs6 7yf","b14 ref","NN13 6PF","PE20 1DA","PE21 7HP","LA12 0UB",
      	"IV2 4ES","GU14 9HA","G776GQ","W12 7PF","N8 9LH","BA4 5HL","PE2 6YL","AL4 9RJ","Je2 4ru","IP2 9QR","NN15 6XU",
      	"RG6 1WG","BS1 6QR","AB25 1LW","NE34 6DZ","HP10 9JL","CF14 1DG","WC1X 8TX","G20 8NX","me17 3ps","EN9 1HN","CR0 1UH",
      	"BN3 6FB","GL2 4AP","PE13 4hl","DA12 2ER","W13 9XG","ha7 2nf","E7 8BA","KT18 7SW","SK22 1BA","N16 9JS","FY8 1BL",
      	"AL1 4QQ","WD25 0NQ","nr23ea","DT7 3UU","N17 8NL","DE24 3AN","B20 2JE","SE16 7TB","FK7 7UP","SE5 8RJ","SO50 8RG",
      	"ST16 1BH","S8 0BW","IP1 3LN","DA26QD","RH6 7JY","CV4 9AP","W9 2QJ","SG12 0XP","SG5 1NQ","RG6 7JX","OX29 9UB",
      	"E3 5AU","N6 4SH","G12 0SA","pe29 2je","YO30 4TD","PE16 6DL","GU27 2PA","EH3 7HA","BN3 3AG","KT12 2JU","IP12 3AW",
      	"EH39QX","Eh151hf","M45 7TH","CR9 2ER","S65 2UR","NW10 3SD","CM8 1DG","TW18 4SN","BN2 1AQ","LD3 8EJ","TN31 7NL",
      	"CA2 4PZ","N16 9AY","LS6 4SH","N7 0QH","LS25 7JE","CO6 1DD","N10 3RD","GU15 3AE","EX39 1JD","CV5 7HH","EC4A 4TR",
      	"W12 7GF","SW16 2BQ","E11 1JX","NG6 9JF","W1F 8AJ","N4 1DJ","EN1 4UE","W5 5RA","RH11 7TD","GU125JF","WN8 7AA",
      	"BD7 3AE","NE7 7TD","E17 8AQ","BA11 1PY","PO4 8PX","G42 9DU","LS196QN","B68 0ES","BN3 5SP","SW3 6BU","KT6 6QZ",
      	"EC2Y 8BA","GU29 9JD","br2 8pf","BT62 1PY","SE18 3PX","SE8 5NA","SE5 8RQ","NE34 0EE","SW2 5EA","NG9 1RJ","sw153ab",
      	"CT6 7BF"
      );

      $us_postalcodes = array(
      	"11222","10011","02143","30316","11211","11226","11215","98052","11216","11231","60601","11238","60647","20011",
      	"10022","10010","10037","94105","90064","98281","11225","11216-2636","94086","91364","10003","98008-3958","11213",
      	"94103","95126","94608","11233","02176","94115","70119","94121","55108-2607","20003","11423-3327","19106","60659",
      	"90245","47405-7107","97213","97206","85283-2777","97405","94065-1175","94123","78681-6202","75206","11218","10019",
      	"11208","94070","90032-1321","91505-1239","30005","90025","10032","94114","10026","10002","90034","11201","90042",
      	"30328-1909","90230","95691-5884","98115","78701","95014","07644-1106","98008-2526","97203","33445","94706",
      	"95991-8473","08540","98118","10001","94117-4419","94131","02116","90067-5010","19446","11216-4801","11211-4525",
      	"48202","94107","11218-1749","07024","11222-1517","98230","28123-0007","95014-2083","85382-3514","94404","10034",
      	"91780","90015","60640","11413","90049","61102-3003","89139-0112","20037-2549","49009-4123","95008-5806","11218-2425",
      	"02176-2427","45342-5217","78723-2212","60637-1610","30309-3870","19462-2119","95126-5319","12586-2016","07747",
      	"33183-4152","98117-4034","78731-2545","33434-4442","90807-3611","76522-3573","11215-2456","91505-4118","98115-7140",
      	"75082-2681","98155","94702-2124","45240-3001","90232-3234","94588-2630","27608-2623","90280-2827","11231-1277",
      	"89002","10013-2381","28054-0026","89431-1899","15209-2091","91606-5256","97303","94103-5864","77063-1503","90290",
      	"53218-4020","01801-4808","11243-2919","60202-3628","60637-2805","22301-1731","20002-2442","98052-5619","90004-3806",
      	"23663-1430","97008-5311","11216-1110","46322","01236-0861","44515-1735","91801-1868","11217-3209","90039-2510",
      	"10034-3944","60503-5768","92649-4541","98109-1752","76126-2465","97213-4367","11203","08021-6826","14223","20910",
      	"46220-3062","94928-8167","98103-4838","99201-1508","30303-2558","98115-6903","07095-2601","95747-9093","94805-1919",
      	"94080-6807","95020-8507","93546-2966","11590-1214","46563-1039","46237-9767","19012-1227","11237-1521","61761-2330",
      	"94536-2637","98664-6122","48198-3293","92868","10469-5210","07731-5033","50213-1738","60647-3917","60640-3228",
      	"23225-2323","60612","75052-6680","11218-5046","48170-4469","01940-2202","95123","22202-2843","78759-8736","37405",
      	"78681-2151","97206-8239","91403-1275","91602","27516-8459","53711-2626","80917-5248","28205-1244","04849-5225",
      	"94131-2214","85650-6615","98467-3316","11420-4106","94501-4848","10003-2740","84129-3011","92105","94608-3677",
      	"10960-3404","11215-1768","01960-5738","85710-5344","77005-1853","90069-2132","98188-2479","19147-3517","95032-1210",
      	"85201-6278","44146-3976","93405","02145-2433","90056-1330","08108-1446","78620","94568-4227","11218-1526","90025-4150",
      	"02871-4309","94301","02445-6825","17087","20737-2051","98199-2304","93534-1957","49001-4306","20011-3232","33055-3839",
      	"70130-5414","10011-4135","48198-3068","28804-2983","97205-1819","21218-4709","19038","58703-0751","60136-4083",
      	"02155-6031","11779-1946","98108-1535","16803-1172","89156","90403-4446","47404-5005","11550-2725","30324-6310",
      	"98107-4181","60615-5580","11238-1156","15237-2329","02184-1707","73034","22192-2441","02747-3602","45215-2502",
      	"94591-7037","90291-2601","18302-7729","60301","81501-2447","20001-1069","06854-1402","90034-6471","97089-8225","78741",
      	"11368-3933","53713-2130","02863","75216-3432","85306-5030","98375-2376","10024-6821","98103-5751","47403-1476",
      	"36104-5125","31907-6885","94609-1643","84115-1930","10014-3336","95843-5321","98144-6129","95032-1815","48340-3054",
      	"80301-2482","94103-3139","75070-8658","10804-1004","36695","60638-5619","11205-3935","94107-3159","21030-1369",
      	"30084-5342","52803","94591-6327","10075-1255","18974-4377","02140-1318","32817-1898","10009-8974","77379-7062",
      	"11701-4229","11249-6104","90266-3427","11372-2705","53211-1420","10128","46038","08854-1451","92596-8296","02145-3027",
      	"77019-5665","11211-6780","85234-3032","60471-3067","95828-6703","44107-4631","90036-3853","10514-2730","90504-2233",
      	"11375","53716-1728","02912-0001","48221-1332","02472-2314","80222-6922","90241-4912","10029-3311","90017-5426",
      	"11217-3119","80221-1121","11226-3960","90026-6615","94103-2284","94305-3001","10009-2626","10001-2900","74066-7872",
      	"89119-7487","98115-3128","21236-4739","20006-5292","40502-1628","02215-4723","33545","77059","02903-1792","11215-4245",
      	"63108-1113","11215-2311","07036","13617-4111","98105-3530","87144-5347","92243-4900","22314-3446","29670-9255",
      	"90026-6699","90601-3765","01830-2724","80203-2596","98281-9007","29403-3523","07208-3525","80246-1106","11102",
      	"80503-9228","77003-5309","30344-2752","23669-4554","94609-1742","66049-5815","10036-2342","21702-4586","60622-5126",
      	"11706-8529","02116-3740","40517-1643","85015-6025","07008-2748","19041","95128-4731","43202-1237","02134-5007","95050",
      	"88012-7476","94024-0754","96826-3072","98006-1760","27612-6541","10459-6100","11217-1486","05495-7435","92114-7829",
      	"33145-3608","90029-3714","10025-7077","94133-2317","13838-1551","10570-3414","07307-4116","48108-1709","65810-2557",
      	"94555-3228","29063-8065","95834","07631-1617","49008-3902","30045-2218","94598-1738","39211-3436","23322-8619",
      	"08901-3100","11201-2905","94117-2215","33605-3944","80525-5873","92882-5866","98102-4023","92114-7021","29690-1822",
      	"03894","20011-4955","20815-3048","91604-1661","91204-2918","98028-4301","98118-2410","98146-3807","90066-7092",
      	"10009-2622","60615-5517","98119-2821","11233-4215","10003-6739","95113","18902","48067-1378","90039-1629","94550-5113",
      	"29466-7910","92101-2348","12180-5534","48239-3513","10065-7634","55604-1074","08690-2466","11552-2501","96822-1414",
      	"48178-8825","94014-1466","08536-2106","21229-4516","02142-0019","80516","19004-1012","50266-6323","06470-2003",
      	"98133-6315","60304","07470-7016","21218-5211","49093-1429","98133-6702","80503-7704","53210-1037","95073-9725",
      	"91607-1228","02906-3123","92007-2420","94403-1230","84124","68123-1408","78724-6128","07305-1159","46240-3314","11751",
      	"10533-1517","90232","97034-2345","30318-5339","94108-1579","37203-7508","33076-1730","10029-1610","75063-4246",
      	"07011-3807","78745-4496","97008-9400","48823-2606","55305-1910","11104-1357","98052-2332","98136-1323","45414-5433",
      	"10025-3520","75756-0300","60618-5914","11201-4697","10001-7504","98117-5939","97266-2021","30067","11550-4712",
      	"60201-1753","90272-4304","90403-2205","60004-6225","03038-4616","53572-2364","90043-3612","56074-0323","47456-8617",
      	"37174","30328-2836","95127","78634-5348","10463-3708","25438-5741","45246","21217-4816","98177-4826","04103-4020",
      	"11230-4120","94706-1439","32407-2468","91343","94040","92691-4211","32789-1627","43221-2590","64064-1389",
      	"61704-4804","30317-3246","14215-3530","33040-3413","27705-5460","02135-7247","94607-3655","05452-4354","11358-3246",
      	"22727","08022-9750","07446","43026-8291","78703-0043","10128-4864","19057-2511","93309-1285","01005-9313",
      	"53703-3825","90026-1926","94103-1519","11233-1701","48176-1646","21727-9103","33606-2343","19468-1746","46011",
      	"98133-6964","04037","33145-1769","93103-2028","20832-1641","80304-0963","02472","93063-3405","95816-5317",
      	"32907-2019","77205-2448","22043-2593","11701","92131-6019","22312-2588","10034-2896","97202-6128","90019-5847",
      	"27278-9171","97204-1622","33304-2968","05033-0691","98029","28314-5033","11211-5078","40509-4555","46280-2727",
      	"48220-3153","11763-2121","10460","55105-2330","80304-3132","76051-3107","78701-3999","33191-1005","94619-1920",
      	"02130-4440","08103-2931","19020-4077","80302-7922","32603","01501-3335","20603-5935","30126-2576","94133-2257",
      	"55406-2229","34949-2904","92113-5003","80026-9372","46545-5954","90016-5115","94116-2611","30017-1936","83642-2033",
      	"90004","07042-4016","30265-3311","11758-1250","78702-1611","90026-4126","27406-8693","10472-4802","98665-0391",
      	"90291-4379","11218-3401","95051-4430","90717-2864","20011-6630","93036-6328","91501-1412","19147-6524","98168-2770",
      	"94103-5232","11207-8638","60608-2135","27540-9184","30126-7202","80203-1326","11205-4724","07055-2248","80401-5228",
      	"80203-1219","95002","27704-5237","19801-2106","90403-4209","10027-5845","43230-1182","11249-2917","19001-4214",
      	"48915-1661","33172-2191","10002-7516","11216-1111","67205-2007","33610-5956","30269-2407","11233-1801","91977-6206",
      	"60618-4809","46208-2657","20002-2136","77802-6064","94086-6212","85339-6941","21223-2535","11768-3428","80031-7185",
      	"98188-7778","91364-2811","80924-2924","10037-1955","11238-5312","19063-1138","30092-3156","60622-8308","51501-5670",
      	"21144","21209-3626","97215-1246","07058-9345","94706-1835","97232-3188","97202-7317","46228-2082","92107-2555",
      	"55405-3455","92210-8743","98136-1030","92107-1814","98033-8031","91602-2427","92104-3449","18040-8264","94509-7389",
      	"90011-3929","55733-9703","19130-1227","94705-2608","90014-1928","55418-1228","20772","05482","19403-3129","94070-6200",
      	"94110-2926","60628","81615-7105","02601","91711-3646","78745-6465","90041-2105","94122-1724","19505-9115","94539-6057",
      	"92555-4507","20037-3009","97206-0850","92113","84101","45236-3316","01002-9760","19085-1138","94103-4931","98112-5259",
      	"20015-1101","07083-7016","96826-3629","01510-1442","90717-2002","11216-5387","53202-1749","33415-8927","18966-5318",
      	"85003-1457","15237","19608-8920","19148-2423","94608-1107","02446-2123","11238-1499","60115-8522","11701-1528",
      	"90640-5711","94566-6891","39211-2129","94561-1796","63135-1756","96817-1208","02718-1003","22015-3432","10453-5419",
      	"80238-2906","94107-4141","20176-7443","10003-5846","48187-2656","93955","94122-3554","95831-4047","06902-1016",
      	"20772-6340","94612-4683","75054-0140","91605-2810","23059-7401","11238-4998","54901-4023","60651-2400","15218",
      	"97370-3904","07093-8384","85716-5653","19026-1103","47119-8759","97214-3344","34105","70809-9636","80923-5449",
      	"97213-2125","11704","19713-3507","98144-5917","42567-0057","98155-5240","60643-2704","98125-3423","30260-3313",
      	"60026-1514","41048-8607","49001-5438","33150-2426","28334-2101","20872-2626","11102-1328","89102-8558","10002-8030",
      	"60642-5839","94030-1618","30135-2002","30316-2521","11510-1925","02780-1278","98116-5833","46260-4242","20186-3420",
      	"92126-5432","98117-5210","45220-2508","94010-8310","90291","02111","55419-5429","56362-1219","60201","08904-2228",
      	"20011-5804","20010-2314","46360-9760","27502-1201","95616-6602","28204","94609-3009","22209","90712-1163","78613-4378",
      	"47401-6870","27405-2794","30135-7636","14085-9447","10033-8403","96813-3201","94044-4115","11510-2335","40502-2438",
      	"90630-4162","94542-2375","23059-4554","11561","21217-2731","33441-7330","16601-3452","90012-2503","35214-1708",
      	"33138-3008","96824-0200","90292-6348","11238-4403","12721-4208","10011-3336","90291-5440","23222","48911-5439",
      	"30062-3267","20002-5216","85086-2315","92592-5618","55418-2610","63108-2326","80205-4619","91406-5518","94107-2913",
      	"02021-2817","98036-8679","30152-3395","94708-2107","98118-2515","70775-4325","89147-7453","30062-5792","85023-3686",
      	"43215-5709","80211-3719","68007","02921-1239","29403-3620","85210-4994","76182-6051","90036-2059","20724","55407-2031",
      	"91316","23464-7435","46825-3529","07945-3007","33558-5028","27405-3546","08873-2354","02138-1202","60612-1260",
      	"54403-2366","11720-1723","57201","43213-6649","37209-4708","20910-5353","98122-3666","92337-1021","03243-3437",
      	"07054-2944","33172-7712","11223-1006","33431-6723","94612-1773","89501","90013-2217","33063","87505-3246","97202-2752",
      	"75218-4144","90402-2515","22213-1029","10589-3022","91914","10002-6488","85704-6975","11201-3861","90026-2903",
      	"78750-1455","96819-2545","11379-2348","12972-4968","94610-3332","90039-1933","98109-4237","10026-4159","94102-6308",
      	"10603-2828","98682-3870","33604-6207","94602-4036","53705","94582-2505","12184-5317","20617-2117","20910-1150",
      	"84047-2389","85258-2358","29414-7538","94131-1632","03102-2930","14020-9624","98144-4867","11216-1957","11231-5096",
      	"76227-3896","11238-5662","11224-3843","20732-3512","97201-2276","08850-1414","20003-4718","02346","29403-4364","20774",
      	"11103-3922","15201-2922","10473-1327","46052-8161","60302-1349","94043-1351","48108-1766","08534-5246","85138-5330",
      	"11215-3913","01915-4420","11746-1352","80202-1487","08873-4968","14206-3145","94103-6403","60626-5079","90036-1715",
      	"75093-5028","90638-4845","33186-5596","22044-1422","20003-3375","43230-4378","47201-5519","94085-3334","81631-5349",
      	"33178-1314","48313-3557","94945-2508","05602-2519","06877-5606","78723-2214","46235-3619","08054-3256","29223",
      	"33618-1908","02118-2024","27707-1038","11215-2840","98109-2003","03864-7253","35810-1309","11225-2716","94501-2418",
      	"20015-1513","53716-1226","94134-1810","31210-0881","96322-0043","53715-1523","94559-3572","97034","33712-2530",
      	"10002-5258","90077","59102-6324","80108-9196","01821-1445","11238-4942","02135-3926","29412-8651","90027-1489",
      	"53210-1717","59106-2496","87110-1603","94501-2702","94607-1932","98121-1172","45236-3433","94706-1326","11375-6561",
      	"55417-2226","12561-1926","98058-5374","45208-1523","91208-2428","94903-2669","55124-6519","90065-5046","15206-5215",
      	"94102-4392","91101-6127","09180-0008","01867-3251","94112-1602","33614-3400","55414-2520","37206-2035","01876-2102",
      	"77584-8702","85743","11225-5971","90068","07044-2518","60640-3835","12443-6032","35806-1817","90405-5277","91311-7505",
      	"90230-4477","48322-1350","11231-5008","10570","07070-2609","60618-7562","11217-1014","33067-1974","49008-2406",
      	"92886-8611","20010-2679","94931","01568-1600","60162-2132","37220-1945","90032-1204","29681-5249","37659-4782",
      	"94503-4190","90230-5060","58501-4743","02494","44511-3015","98059-7030","55414-1220","11205","02127-1060","33020-3606",
      	"33619-5006","19124-2319","90403-5114","07076-2824","21144-3320","78702-5423","70122-2753","01060","98115-5706",
      	"54301-1906","02143-1812","33625-6581","98034-4607","10010-1706","19120-2227","80112-4669","87110-3006","48105-1589",
      	"48187","08012-4435","29212-2403","44221-1841","28379-3617","96819-4912","07512-1902","91362-5939","70002-5807",
      	"78759-5669","94122-4024","38125-4776","10009","80212-2229","80906-3734","14203-1252","20016","75604-2148","90210-1326",
      	"85224-1226","23185-4247","02149-3102","95112-3121","14580-9174","20737-3037","21012-2615","78621","98045-9656",
      	"08904-2201","20832-1567","10576","95124-2024","28590-7910","21046-1005","32935-8610","94030-2721","98118-3250",
      	"48154-5140","11201-3053","78641-8688","97005-0979","33330-1328","78728","30329","94549-3885","94061","86004-5007",
      	"53211-3050","60647-4421","11230","88012-7237","60060-1023","94118-2822","95822-3029","80301-2155","85022-5046",
      	"20852-4673","94941-2101","48168-2320","77077-1821","11205-4680","80521-1862","89183-8000","60626-1020","11226-6211",
      	"10036-3697","30034-3732","95050-6138","93311","07012-1453","78660-2344","43220-4120","90302-1119","98230-6602",
      	"30080-3711","30342-4332","30043-4954","97225-3208","90040-3946","43920","40222-4504","78735-7914","98225-5314",
      	"78731-1161","91702-4238","60657-3625","18301-9376","78245","60619","30312-3409","08610-3239","78681-7425","11232-3818",
      	"37130-3020","01754-2300","48504-3238","94706-2119","12919-4440","10003-1759","33155-4925","94062","87507-3422",
      	"98122-2149","78521","10031-8707","28027-3604","02492-1219","79415-1105","65203-1892","60071-0215","77040-2812",
      	"50801-1613","94404-5060","97405-3766","10029","30338-3081","98122-3854","93117-4209","77545-9721","07042-4908",
      	"48307-5078","36870-4802","40517-4169","52317-9666","20148-5706","92115","44077-5242","34683-2517","53225-4540",
      	"37127-6800","28451-7777","98177-4806","92674-3333","81201-3115","19146-3119","21217-3615","30308-1331","94103-3914",
      	"85224-4341","07010","21210-2411","60614-3108","80204-5137","07040-1634","92867-5803","21162-1151","02155-3570","27513",
      	"93401-5158","32703-1911","80439-7973","20008-3524","44663-1349","75181-2135","21228-4332","12309-1238","94568-4264",
      	"94110-3436","70131-1908","95827-1012","11225-3417","80513-1126","75201-2824","37128-2769","10018","93401","98109-2941",
      	"55413-1149","92703-4542","98342-0596","91411-3602","94110-2825","55404-2823","03455-2810","07642-1353","59808-5915",
      	"77550","11377-7705","91351-5531","28227-7518","60647-3839","29403-4633","20721-2274","10035-2778","60148-1549",
      	"11215-3803","02110","80516-4613","60637-5205","29229-9620","14150-1002","30030-4306","11211-5505","90250-3395",
      	"22102-3830","30067-5006","92011-4208","94578-2336","11230-1464","95677-1947","94301-2218","33014","75243-3248",
      	"92139","72076-1014","90064-4352","11226-4546","10940","80525-2184","76262-9768","60626-3412","90039-1614","90814-4544",
      	"98134-1446","60622","91602-2377","95630-3815","92129-1020","67204-4429","60441-4654","19122-3927","11231-5046",
      	"94619-2128","61801-3136","93101-3328","97520-1587","95127-1043","04096-7932","08109-4533","77098-4299","80466-9762",
      	"61821-1700","30058-1873","92130-3229","62049-3009","48173-8737","97217-4952","06360-6817","94040-1411","20640",
      	"03301-2224","15219-4267","60513-1440","21742-9753","94086-9051","98225-4908","78228-1954","28213-5341","11249-3928",
      	"11238-1327","44122-8500","98119","60647-2007","20901-2213","94002","06516-5606","20191-4208","11215-6024","78750-1889",
      	"07002-1413","94602-1907","90278-2229","90042-1028","55410-1757","77047-6818","61201-3717","35242-1802","53158-5608",
      	"48103-2709","98021-8119","02909","30309-3816","52405-2828","91326-2340","32246-7319","97206-7769","77035-3011",
      	"10012-2614","92869-5339","98107-2444","20005-4232","12401-6101","11369","98107-5013","85354","94602-1929","30328-3719",
      	"30326-1027","10011-4113","02149-2512","11510-1423","43082-7442","06525","11216-2254","31201","45209-1410","98370-7143",
      	"14304-1558","73012-4404","27713-6124","67208","11933-1044","77433-6241","98117-4543","46236-7311","90038-2702","46254",
      	"11803-6402","91344-1122","95131-2935","92276","07650-2135","53717-1953","92154-2313","07928-2011","10538-2713",
      	"97212-4336","53207-1804","63034-1041","32746-6594","60201-4387","27278-7569","22201-2514","18355-7707","49615","46077-1836",
      	"52317-9335","90230-8275","98122-6035","95054-1390","11226-3106","91773-1117","92114-6042","17111-4147","02906-3618",
      	"84004-1915","94960-2306","60652-2561","71112","20850","84102-3271","33462-3037","60120-4750","27510","32801-7303",
      	"98117-4848","60626-3174","02879-2368","46410-8494","60406","11216-6138","94109","60653-2865","94116-1735","07071-2413",
      	"18020-9635","06470-2468","90004-1416","40213-1742","90803-5936","22204-5866","60462-6036","07302-3414","60194","48334-4849",
      	"23436-1003","60618-6797","98004-6287","07102-4124","97206-8166"
      );

      $tiers = array(61,62,64,67,68,69,70,71,72,73,74,75,76,77,78,79);
      foreach ($tiers as $tier) {
         $result = $this->db->setData(
             'external_fulfillment_orders',
             array(
                'complete' => 1472225831
             ),
             array(
                'tier_id' => array(
                     'condition' => '=',
                     'value' => $tier
                )
             )
         );
      }

      $prices = array(
         array(63,6500),
         array(65,1500),
         array(66,7000)
      );
      foreach ($prices as $details) {
         $result = $this->db->setData(
             'external_fulfillment_orders',
             array(
                'price' => $details[1]
             ),
             array(
                'tier_id' => array(
                     'condition' => '=',
                     'value' => $details[0]
                )
             )
         );
      }

      $uk_result = $this->db->getData(
           'external_fulfillment_orders',
           '*',
           array(
               "complete" => array(
                   "condition" => ">",
                   "value" => 0
               ),
               "shipping_postal" => array(
                   "condition" => "=",
                   "value" => ""
               ),
               "email" => array(
                   "condition" => "LIKE",
                   "value" => "%.uk%"
               )
           )
      );
      if ($uk_result) {
         foreach ($uk_result as $order) {
            $result = $this->db->setData(
                'external_fulfillment_orders',
                array(
                   'shipping_postal' => $uk_postalcodes[rand(0, count($uk_postalcodes) - 1)],
                   'shipping_country' => 'GB'
                ),
                array(
                   'id' => array(
                        'condition' => '=',
                        'value' => $order['id']
                   )
                )
            );
         }
      }

      $us_result = $this->db->getData(
           'external_fulfillment_orders',
           '*',
           array(
               "complete" => array(
                   "condition" => ">",
                   "value" => 0
               ),
               "shipping_postal" => array(
                   "condition" => "=",
                   "value" => ""
               ),
               "email" => array(
                   "condition" => "NOT LIKE",
                   "value" => "%.co.%"
               )
           )
      );
      if ($us_result) {
         foreach ($us_result as $order) {
            if ($order['complete'] != 1472225831) {
               $result = $this->db->setData(
                   'external_fulfillment_orders',
                   array(
                      'shipping_postal' => $us_postalcodes[rand(0, count($us_postalcodes) - 1)],
                      'shipping_country' => 'US'
                   ),
                   array(
                      'id' => array(
                           'condition' => '=',
                           'value' => $order['id']
                      )
                   )
               );
            }
         }
      }
   }

} // END class
?>
