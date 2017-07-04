<?php

namespace App\Tests\Plants;

use CASHMusic\Core\CASHSystem;
use CASHMusic\Core\CASHRequest;

CASHSystem::startUp();

describe('Start session', function () {
    given('session', function() {
        $request = new CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'startjssession'
            )
        );

        if ($request->response['payload']) {
            $s = json_decode($request->response['payload'],true);
            return $s['id'];
        }
    });
    describe('session', function () {
        it('return session', function () {
            expect($this->session)->toBeA("string");
        });
    });
});
/*
describe('Create transaction', function () {
    given('transaction', function() {
        return new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'addtransaction',
                'user_id' => 1,
                'connection_id' => 1,
                'connection_type' => 'com.stripe',
                'service_timestamp' => 'string not int — different formats',
                'service_transaction_id' => '123abc',
                'data_sent' => 'big JSON',
                'data_returned' => 'also big JSON',
                'successful' => -1,
                'gross_price' => 15,
                'service_fee' => 0.52
            )
        );
    });
    describe('transaction', function () {
        it('creates transaction', function () {
            expect($this->transaction->response['payload'])->toBeA("array");
        });
    });
});*/