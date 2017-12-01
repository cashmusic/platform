<?php

namespace CASHMusic\Plants\Commerce\Stems;

use CASHMusic\Entities\CommerceItemVariant;

use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;

use Exception;

trait Variants {
    protected function addItemVariants(
        $item_id,
        $variants
    ) {

        $item_details = $this->getItem($item_id);

        if ($item_details) {
            $variant_ids = array();

            foreach ($variants as $attributes => $quantity) {
                try {
                    $variant = $this->orm->create(CommerceItemVariant::class, [
                        'item_id' => $item_id,
                        'user_id' => $item_details['user_id'],
                        'attributes' => json_decode($attributes, true),
                        'quantity' => $quantity
                    ]);

                } catch (Exception $e) {
                    CASHSystem::errorLog($e->getMessage());
                }

                if (!$variant) {
                    return $this->error(400)->message('There was an error saving this product variant.');
                }

                $variant_ids[$attributes] = $variant->id;
            }

            $this->updateItemQuantity($item_id);

            return $variant_ids;
        } else {
            return false;
        }
    }

    protected function getItemVariants($item_id, $exclude_empties=false, $user_id=false) {
        $conditions = array(
            "item_id" => $item_id
        );

        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        $item_variants = $this->orm->findWhere(CommerceItemVariant::class, $conditions, true);

        if ($item_variants) {
            $variants = array(
                'attributes' => array(),
                'quantities' => array(),
            );

            $attributes = array();

            foreach ($item_variants as $item) {
                if (is_cash_model($item)) {
                    // first try json_decode
                    $attribute_array = $item->attributes;
                    if (!is_array($attribute_array)) {
                        // old style keys, so format them to match JSON
                        $attribute_array = array();
                        $attribute_keys = explode('+', $item->attributes);
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
                        $attributes[$key][$type] += $item->quantity;
                    }
                    if (!($item->quantity < 1 && $exclude_empties)) {
                        $variants['quantities'][] = array(
                            'id' => $item->id,
                            'key' => json_encode($item->attributes),
                            'formatted_name' => $this->formatVariantName($item->attributes),
                            'value' => $item->quantity
                        );
                    }
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

        if (!is_array($name)) {
            $name_decoded = json_decode($name, true);
        } else {
            $name_decoded = $name;
        }

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

    protected function editItemVariant($id, $quantity, $item_id, $user_id=false) {

        $conditions = array(
            "id" => $id
        );

        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        $updates = array(
            'quantity' => $quantity
        );

        $item_variant = $this->orm->findWhere(CommerceItemVariant::class, $conditions);

        if ($item_variant->update($updates)) {
            $this->updateItemQuantity($item_id);

            return true;
        }

        return false;
    }

    protected function deleteItemVariant($id, $user_id=false) {

        $conditions = array(
            "id" => $id
        );

        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        $item_variant = $this->orm->findWhere(CommerceItemVariant::class, $conditions);

        if ($item_variant->delete()) {
            return true;
        }

        return false;
    }

    protected function deleteItemVariants($item_id, $user_id=false) {

        $query = $this->db->table('commerce_item_variants')
            ->where('item_id', '=', $item_id);

        if ($user_id) {
            $query = $query->where('user_id', '=', $user_id);
        }

        if ($query->delete()) {
            return true;
        }

        return false;
    }
}