<?php
namespace Kahlan\Spec\Suite\Matcher;

use Kahlan\Matcher\ToBeLessThan;

describe("toBeLessThan", function () {

    describe("::match()", function () {

        it("passes if 1 is < 2", function () {

            expect(1)->toBeLessThan(2);

        });

        it("passes if 0.999 < 1", function () {

            expect(0.999)->toBeLessThan(1);

        });

        it("passes if 2 is not < 2", function () {

            expect(2)->not->toBeLessThan(2);

        });

    });

    describe("::description()", function () {

        it("returns the description message", function () {

            $actual = ToBeLessThan::description();

            expect($actual)->toBe('be lesser than expected.');

        });

    });

});
