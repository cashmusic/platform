<?php
namespace Kahlan\Spec\Suite\Matcher;

use Kahlan\Matcher\ToBeTruthy;

describe("toBeTruthy", function () {

    describe("::match()", function () {

        it("passes if true is truthy", function () {

            expect(true)->toBeTruthy();

        });

        it("passes if 'Hello World' is truthy", function () {

            expect('Hello World')->toBeTruthy();

        });

        it("passes if 1 is truthy", function () {

            expect(1)->toBeTruthy();

        });

        it("passes if [1, 3, 7] is truthy", function () {

            expect([1, 3, 7])->toBeTruthy();

        });

    });

    describe("::description()", function () {

        it("returns the description message", function () {

            $actual = ToBeTruthy::description();

            expect($actual)->toBe('be truthy.');

        });

    });

});
