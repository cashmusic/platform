<?php

namespace CASHMusic\Plants\Commerce\Stems;

use CASHMusic\Entities\CommerceItem;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;

use Exception;

trait Items {
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

                $request = $this->request('asset')
                                ->action('getasset')
                                ->with(['id' => $fulfillment_asset])->get();

                // we've got the request, we need to make sure the properties actually exist

                $fulfillment_asset_data = $request['payload'];
                if (is_array($fulfillment_asset_data->metadata)
                    && isset($fulfillment_asset_data->metadata['cover'])
                ) {
                    $descriptive_asset = $fulfillment_asset_data->metadata['cover'];
                }
            }
        }

        try {
            $item = $this->orm->create(CommerceItem::class, [
                'user_id' => $user_id,
                'name' => $name,
                'description' => $description,
                'sku' => $sku,
                'price' => $price,
                'shipping' => $shipping,
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
            ]);
        } catch (Exception $e) {
            CASHSystem::errorLog($e->getMessage());
            return false;
        }

        return $item->id;
    }

    protected function getItem($id,$user_id=false,$with_variants=true) {
        if ($user_id) {
            $item = $this->orm->findWhere(CommerceItem::class, ['id'=>$id, 'user_id'=>$user_id] );
        } else {
            $item = $this->orm->find(CommerceItem::class, $id );
        }

        if ($item) {

            $item = $item->toArray();

            if ($with_variants) {
                $item['variants'] = $this->getItemVariants($id, $user_id);
            }

            return $item;
        } else {
            return false;
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

        $item = $this->orm->findWhere(CommerceItem::class, $conditions);

        try {
            $item->update($final_edits);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    protected function deleteItem($id,$user_id=false) {
        $conditions = array(
            "id" => $id
        );

        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        if ($item = $this->orm->findWhere(CommerceItem::class, $conditions)) {
            if($item->delete()) {
                $this->deleteItemVariants($id, $user_id);
                return true;
            }
        }

        return false;
    }

    protected function getItemsForUser($user_id,$with_variants=true) {

        try {
            $items = $this->orm->findWhere(CommerceItem::class, ['user_id'=>$user_id], true);

            if (!$items) {
                return $this->error(404)->message("No items found for this user.");
            }

            $result = [];

            if ($with_variants) {
                foreach($items as $key=>$item) {

                    $result[$key] = $item->toArray();
                    $result[$key]['image_url'] = false;

                    if ($descriptive_asset = $item->descriptiveAsset()) {
                        if (isset($descriptive_asset->location)) {
                            $result[$key]['image_url'] = $descriptive_asset->location;
                        }
                    }

                    $result[$key]['variants'] = $this->getItemVariants($item->id, false, $user_id);
                    $result[$key]['shipping'] = $item->shipping;
                }
            } else {
                foreach($items as $key=>$item) {

                    $result[$key] = $item->toArray();

                    if ($descriptive_asset = $item->descriptiveAsset()) {
                        if (isset($descriptive_asset->location)) {
                            $result[$key]['image_url'] = $descriptive_asset->location;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return $this->error(400)->message($e->getMessage());
        }

        return $result;
    }

    protected function emailBuyersByItem($user_id,$connection_id,$item_id,$subject,$message,$include_download=false) {

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
            //TODO: we need better responses
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

                    $asset_request = $this->request('asset')
                                        ->action('getasset')
                                        ->with(['id' => $item_details['fulfillment_asset']])->get();

                    if ($asset_request['payload']) {
                        $unlock_suffix = 1;
                        $all_assets = array();
                        if ($asset_request['payload']['type'] == 'file') {
                            $message .= "\n\n" . 'Download *|ITEMNAME|*: at '.CASH_PUBLIC_URL.'/download/?code=*|UNLOCKCODE1|*';
                            $html_message .= "\n\n" . '<p><b><a href="'.CASH_PUBLIC_URL.'/download/?code=*|UNLOCKCODE1|*">Download *|ITEMNAME|*</a></b></p>';
                            $all_assets[] = array(
                                'id' => $item_details['fulfillment_asset'],
                                'name' => $asset_request['payload']['title']
                            );

                        } else {
                            $message .= "\n\n" . '*|ITEMNAME|*:' . "\n\n";
                            $html_message .= "\n\n" . '<p><b>*|ITEMNAME|*:</b></p>';

                            $fulfillment_request = $this->request('asset')
                                                    ->action('getfulfillmentassets')
                                                    ->with(['asset_details' => $asset_request['payload']])->get();

                            if ($fulfillment_request['payload']) {
                                foreach ($fulfillment_request['payload'] as $asset) {
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

                                $addcode_request = $this->request('asset')
                                                        ->action('addlockcode')
                                                        ->with(['asset_id' => $asset['id']])->get();
                                $all_vars[] = array(
                                    'name' => 'assetname'.$unlock_suffix,
                                    'content' => $asset['name']
                                );
                                $all_vars[] = array(
                                    'name' => 'unlockcode'.$unlock_suffix,
                                    'content' => $addcode_request['payload']
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
                                    $addcode_request['payload'],
                                    $recipient_message
                                );

                                $unlock_suffix++;
                            }
                            if ($addcode_request['payload']) {
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
                $success = CASHSystem::sendMassEmail(
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

    protected function updateItemQuantity(
        $id
    ) {

        $result = $this->db->table('commerce_item_variants')
            ->select('SUM(quantity) as total_quantity')
            ->where('item_id', '=', $id)->get();

        if (!$result) {
            return false;
        }

        $item = $this->orm->find(CommerceItem::class, $id );

        if ($item->update([
            'available_units' => $result[0]->total_quantity
        ])) {
            return $item;
        } else {
            return false;
        }
    }
}