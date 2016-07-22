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
            'additem'             => array('addItem','direct'),
            'additemvariants'     => array('addItemVariants','direct'),
            'addorder'            => array('addOrder','direct'),
            'addtocart'				 => array('addToCart',array('get','post','direct','api_public')),
            'addtransaction'      => array('addTransaction','direct'),
            'cancelorder'			 => array('cancelOrder','direct'),
            'deleteitem'          => array('deleteItem','direct'),
            'deleteitemvariant'   => array('deleteItemVariant','direct'),
            'deleteitemvariants'  => array('deleteItemVariants','direct'),
            'editcartquantity'	 => array('editCartQuantity',array('get','post','direct','api_public')),
            'editcartshipping'	 => array('editCartShipping',array('get','post','direct','api_public')),
            'edititem'            => array('editItem','direct'),
            'edititemvariant'   	 => array('editItemVariant','direct'),
            'editorder'           => array('editOrder','direct'),
            'edittransaction'     => array('editTransaction','direct'),
            'emailbuyersbyitem'	 => array('emailBuyersByItem','direct'),
            'emptycart'				 => array('emptyCart',array('get','post','direct','api_public')),
            'formatvariantname'   => array('formatVariantName','direct'),
            'getanalytics'        => array('getAnalytics','direct'),
            'getcart'				 => array('getCart','direct'),
            'getitem'             => array('getItem','direct'),
            'getitemvariants'     => array('getItemVariants','direct'),
            'getitemsforuser'     => array('getItemsForUser','direct'),
            'getorder'            => array('getOrder','direct'),
            'getordersforuser'    => array('getOrdersForUser','direct'),
            'getordersbycustomer' => array('getOrdersByCustomer','direct'),
            'getordersbyitem'		 => array('getOrdersByItem','direct'),
            'getordertotals' 		 => array('getOrderTotals','direct'),
            'gettransaction'      => array('getTransaction','direct'),
            'finalizepayment'     => array('finalizePayment',array('get','post','direct')),
            'initiatecheckout'    => array('initiateCheckout',array('get','post','direct','api_public')),
            'sendorderreceipt'	 => array('sendOrderReceipt','direct')
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

    protected function emailBuyersByItem($user_id,$item_id,$subject,$message,$include_download=false) {

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


            $user_request = new CASHRequest(
                array(
                    'cash_request_type' => 'people',
                    'cash_action' => 'getuser',
                    'user_id' => $user_id
                )
            );
            $user_details = $user_request->response['payload'];

            if ($user_details['display_name'] == 'Anonymous' || !$user_details['display_name']) {
                $user_details['display_name'] = $user_details['email_address'];
            }

            //TODO: move these to the outer solar system in their own template

            $recipients = array();
            $tmp_recipients = array();
            $all_orders = $this->getOrdersByItem($user_id,$item_id);
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
                if (file_exists(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php')) {
                    include_once(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php');
                }
                $html_message = Markdown($message);

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
                            $message .= "\n\n" . 'Download fuh at '.CASH_PUBLIC_URL.'/download/?code=*|UNLOCKCODE1|*';
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

                            if (!CASHSystem::sendEmail(
                                $subject,
                                $user_id,
                                $recipient['email'],
                                $recipient_message,
                                $subject,                // this seems more appropriate than hardcoding something
                                false                    // tell sendEmail we're sending a full encoded HTML body
                            )) {
                                //TODO: sendEmail is returning false even though everything is sending correctly.
                                //$success = false;
                            }
                        }
                    }
                }

/*                $mandrill = new MandrillSeed($user_id,$connection_id);
                $result = $mandrill->send(
                    $subject,
                    $message,
                    $html_message,
                    $user_details['email_address'],
                    $user_details['display_name'],
                    $recipients,
                    null,
                    $global_merge_vars,
                    $merge_vars
                );*/
                if (!$success) return false;

                return true;
            }
        } else {
            return false;
        }
    }

    protected function addToCart($item_id,$element_id,$item_variant=false,$price=false,$session_id=false) {
         $r = new CASHRequest();
         $r->startSession(false,$session_id);

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
        $r->startSession(false,$session_id);

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
        $r->startSession(false,$session_id);

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
         $r->startSession(false,$session_id);
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
        $r->startSession(false,$session_id);
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
        $this->startSession(false,$session_id);
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

            $this->startSession(false,$session_id);
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

      $this->startSession(false,$session_id);

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
} // END class
?>
