<?php
namespace Kahlan\Spec\Suite\Matcher;

use Kahlan\Matcher\ToBeFalsy;

describe("toBeFalsy", function () {

    describe("::match()", function () {

        it("passes if false is fasly", function () {

            expect(false)->toBeFalsy();

        });

        it("passes if null is fasly", function () {

            expect(null)->toBeFalsy();

        });

        it("passes if [] is fasly", function () {

            expect([])->toBeFalsy();

        });

        it("passes if 0 is fasly", function () {

            expect(0)->toBeFalsy();

        });

        it("passes if '' is fasly", function () {

            expect('')->toBeFalsy();

        });

    });

    describe("::description()", function () {

        it("returns the description message", function () {

            $actual = ToBeFalsy::description();

            expect($actual)->toBe('be falsy.');

        });

    });

});
