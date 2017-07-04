<?php
namespace Kahlan\Spec\Suite\Matcher;

use Kahlan\Matcher\ToBe;

describe("toBe", function () {

    describe("::match()", function () {

        it("passes if true === true", function () {

            expect(true)->toBe(true);

        });

        it("passes if false === false", function () {

            expect(false)->toBe(false);

        });

        it("passes if 1 === 1", function () {

            expect(1)->toBe(1);

        });

        it("passes if 'Hello World' === 'Hello World'", function () {

            expect('Hello World')->toBe('Hello World');

        });

        it("passes if [1, 3, 7] === [1, 3, 7]", function () {

            expect([1, 3, 7])->toBe([1, 3, 7]);

        });

        it("passes if true is not === false", function () {

            expect(true)->not->toBe(false);

        });

        it("passes if false is not === true", function () {

            expect(false)->not->toBe(true);

        });

        it("passes if 2 is not === 1", function () {

            expect(2)->not->toBe(1);

        });

        it("passes if 1 is not === true", function () {

            expect(1)->not->toBe(true);

        });

        it("passes if 0 is not === false", function () {

            expect(0)->not->toBe(false);

        });

        it("passes if [] is not === true", function () {

            expect([])->not->toBe(true);

        });

        it("passes if [] is not === false", function () {

            expect([])->not->toBe(false);

        });

        it("passes if 'Hello World' is not === true", function () {

            expect('Hello World')->not->toBe(true);

        });

        it("passes if 'Hello World' is not === false", function () {

            expect('Hello World')->not->toBe(false);

        });

        it("passes if 'Hello World' is not === 'World Hello'", function () {

            expect('Hello World')->not->toBe('World Hello');

        });

        it("passes if [1, 3, 7] is not === [1, 7, 3]", function () {

            expect([1, 3, 7])->not->toBe([1, 7, 3]);

        });

        it("passes if ['a' => 1, 'b' => 3, 'c' => 7] is not === ['a' => 1, 'c' => 7, 'b' => 3]", function () {

            expect(['a' => 1, 'b' => 3, 'c' => 7])->not->toBe(['a' => 1, 'c' => 7, 'b' => 3]);

        });

    });

    describe("::description()", function () {

        it("returns the description message", function () {

            $actual = ToBe::description();

            expect($actual)->toBe('be identical to expected (===).');

        });

    });

});
