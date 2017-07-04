<?php

namespace App\Tests\Plants;

use CASHMusic\Core\CASHSystem;
use CASHMusic\Core\CASHRequest;

CASHSystem::startUp();

describe('CASHRequest', function () {
    given('request', function() {
        return new CASHRequest(
            array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'additem',
            'user_id' => 1,
            'name' => "some test shitzzzz",
            'description' => "my descriptionzzzz",
            'price' => 69.69,
            'available_units' => -1,
            'flexible_price' => true,
            'digital_fulfillment' => true,
            'fulfillment_asset' => 1,
            'physical_fulfillment' => true
            )
        );
    });
    describe('instance', function () {
        it('return "CASHRequest" instance', function () {
            expect($this->request)->toBeAnInstanceOf(CommerceItem::class);
        });
    });
});