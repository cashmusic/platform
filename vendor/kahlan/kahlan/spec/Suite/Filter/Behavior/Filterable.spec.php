<?php
namespace Kahlan\Spec\Suite\Filter\Behavior;

use Kahlan\Plugin\Double;
use Kahlan\Filter\MethodFilters;

describe('Filterable', function () {

    beforeEach(function () {
        $this->mock = Double::instance(['uses' => ['Kahlan\Filter\Behavior\Filterable']]);

        allow($this->mock)->toReceive('filterable')->andRun(function () {
            return Filter::on($this, 'filterable', func_get_args(), function ($chain, $message) {
                return "Hello {$message}";
            });
        });
    });

    describe("methodFilters", function () {

        it("gets the `MethodFilters` instance", function () {

            expect($this->mock->methodFilters())->toBeAnInstanceOf('Kahlan\Filter\MethodFilters');

        });

        it("sets a new `MethodFilters` instance", function () {

            $methodFilters = new MethodFilters();
            expect($this->mock->methodFilters($methodFilters))->toBeAnInstanceOf('Kahlan\Filter\MethodFilters');
            expect($this->mock->methodFilters())->toBe($methodFilters);

        });

    });

});
