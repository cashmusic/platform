<?php

/**
 * standalone global helper functions, to unclutter CASHSystem et al
 */

/**
 * @param $model
 * @return bool
 */
function is_cash_model($model) {
    if ($model instanceof \CASHMusic\Entities\EntityBase) {
        return true;
    }

    return false;
}

function cash_model_to_array($array) {
    if (!is_cash_model($array)) {
        foreach ($array as &$item) {
            if (is_cash_model($item)) {
                $item = $item->toArray();
            }
        }
    } else {
        return $array->toArray();
    }
}

function cash_model_column($array_of_models, $column) {
    $columns = [];

    foreach($array_of_models as $model) {
        if (is_cash_model($model)) {
            if (isset($model->{$column})) $columns[] = $model->{$column};
        } else if (is_array($model)) {
            if (isset($model[$column])) $columns[] = $model[$column];
        }
    }

    return $columns;
}

function is_json($string) {

    if(is_array($string) || is_object($string)) return false;

    try {
        json_decode($string);
    } catch (\Exception $e) {
        return false;
    }

    return (json_last_error() == JSON_ERROR_NONE);
}

function format_date($date, $format=false) {
    if (!$format) $format = "F jS, Y";

    return date($format, $date);
}