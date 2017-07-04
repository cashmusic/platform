<?php
namespace Kahlan\Spec\Suite\Matcher;

use Kahlan\Matcher\ToBeCloseTo;

describe("toBeCloseTo", function () {

    describe("::match()", function () {

        it("passes if the difference is lower than the default two decimal", function () {

            expect(0)->toBeCloseTo(0.001);

        });

        it("fails if the difference is higher than the default two decimal", function () {

            expect(0)->not->toBeCloseTo(0.01);

        });

        it("passes if the difference is lower than the precision", function () {

            expect(0)->toBeCloseTo(0.01, 1);

        });

        it("fails if the difference is higher than the precision", function () {

            expect(0)->not->toBeCloseTo(0.1, 1);

        });

        it("passes if the difference with the round is lower than the default two decimal", function () {

            expect(1.23)->toBeCloseTo(1.225);
            expect(1.23)->toBeCloseTo(1.234);

        });

        it("fails if the difference with the round is lower than the default two decimal", function () {

            expect(1.23)->not->toBeCloseTo(1.2249999);

        });

        it("return false if actual or expected are not a numeric value", function () {

            expect("string")->not->toBeCloseTo(1);
            expect(1)->not->toBeCloseTo("string");

        });

    });

    describe("::description()", function () {

        it("returns the description message", function () {

            ToBeCloseTo::match(1.23, 1.22499991, 2);
            $actual = ToBeCloseTo::description();

            expect($actual['description'])->toBe('be close to expected relying to a precision of 2.');
            expect((string) $actual['data']['actual'])->toBe((string) 1.23);
            expect((string) $actual['data']['expected'])->toBe((string) 1.22499991);
            expect((string) $actual['data']['gap is >='])->toBe((string) 0.005);

        });

    });

});
