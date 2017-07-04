<?php
namespace Kahlan\Spec\Suite\Filter;

use Kahlan\Plugin\Stub;
use Kahlan\Filter\Chain;

describe("Chain", function () {

    beforeEach(function () {
        $method = 'message';
        $filters = [
            'filter1' => function ($chain, $message) {
                $message = "My {$message}";
                return $chain->next($message);
            },
            'filter2' => function ($chain, $message) {
                return $message;
            }
        ];
        $params = ['World!'];
        $this->chain = new Chain(compact('filters', 'method', 'params'));
    });

    describe("->params()", function () {

        it("gets the params", function () {

            expect($this->chain->params())->toBe(['World!']);

        });

    });

    describe("->method()", function () {

        it("gets the methods", function () {

            expect($this->chain->method())->toBe('message');

        });

    });

    describe("Iterator", function () {

        it("iterate throw the chain", function () {

            expect($this->chain->current())->toBeAnInstanceOf('Closure');
            expect($this->chain->key())->toBe('filter1');
            expect($this->chain->next())->toBe('World!');
            expect($this->chain->next())->toBe(false);
            expect($this->chain->valid())->toBe(false);

        });

        it("procceses a chain", function () {

            $closure = $this->chain->current();
            expect($closure($this->chain, 'Poney!'))->toBe('My Poney!');

        });

        it("counts the number of filters in the chain", function () {

            expect($this->chain)->toHaveLength(2);

        });

    });

});
