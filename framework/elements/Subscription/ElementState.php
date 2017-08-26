<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 1/25/17
 * Time: 3:32 PM
 */

namespace CASHMusic\Elements\subscription;

use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Core\ElementBase;
use CASHMusic\Elements\Interfaces\StatesInterface;
use CASHMusic\Entities\EntityBase;
use CASHMusic\Plants\Commerce\CommercePlant;
use CASHMusic\Elements\Subscription\ElementData;

class ElementState
{
    protected $state, $element_data, $element_id, $session, $session_id, $user_id, $plan_id, $email_address, $element_user_id, $subscriber_id;

    /**
     * States constructor. Set the needed values for whatever we're going to do to
     * react to the element state
     *
     * @param $element_data
     * @param $session_id
     */
    public function __construct($element_data, $session_id)
    {

    }

}