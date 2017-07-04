<?php
namespace Kahlan\Spec\Suite\Matcher;

use Kahlan\Matcher\ToEqual;

describe("toEqual", function () {

    describe("::match()", function () {

        it("passes if true == true", function () {

            expect(true)->toEqual(true);

        });

        it("passes if false == false", function () {

            expect(false)->toEqual(false);

        });

        it("passes if 1 == 1", function () {

            expect(1)->toEqual(1);

        });

        it("passes if [] == false", function () {

            expect([])->toEqual(false);

        });

        it("passes if 'Hello World' == true", function () {

            expect('Hello World')->toEqual(true);

        });

        it("passes if 'Hello World' == 'Hello World'", function () {

            expect('Hello World')->toEqual('Hello World');

        });

        it("passes if 1 == true", function () {

            expect(1)->toEqual(true);

        });

        it("passes if 0 == false", function () {

            expect(0)->toEqual(false);

        });

        it("passes if [1, 3, 7] == [1, 3, 7]", function () {

            expect([1, 3, 7])->toEqual([1, 3, 7]);

        });

        it("passes if ['a' => 1, 'b' => 3, 'c' => 7] == ['a' => 1, 'c' => 7, 'b' => 3]", function () {

            expect(['a' => 1, 'b' => 3, 'c' => 7])->toEqual(['a' => 1, 'c' => 7, 'b' => 3]);

        });

        it("passes if [] is not == true", function () {

            expect([])->not->toEqual(true);

        });

        it("passes if true is not == false", function () {

            expect(true)->not->toEqual(false);

        });

        it("passes if false is not == true", function () {

            expect(false)->not->toEqual(true);

        });

        it("passes if 2 is not == 1", function () {

            expect(2)->not->toEqual(1);

        });

        it("passes if 'Hello World' is not == false", function () {

            expect('Hello World')->not->toEqual(false);

        });

        it("passes if 'Hello World' is not == 'World Hello'", function () {

            expect('Hello World')->not->toEqual('World Hello');

        });

        it("passes if [1, 3, 7] is not == [1, 7, 3]", function () {

            expect([1, 3, 7])->not->toEqual([1, 7, 3]);

        });

    });

    describe("::description()", function () {

        it("returns the description message", function () {

            $actual = ToEqual::description();

            expect($actual)->toBe('be equal to expected (==).');

        });

    });

});
