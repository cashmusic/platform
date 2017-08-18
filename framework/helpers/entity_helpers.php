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