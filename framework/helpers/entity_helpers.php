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

function is_json($string) {

    if(is_array($string) || is_object($string)) return false;

    try {
        json_decode($string);
    } catch (\Exception $e) {
        return false;
    }

    return (json_last_error() == JSON_ERROR_NONE);
}