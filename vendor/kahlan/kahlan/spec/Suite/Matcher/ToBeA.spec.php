<?php
namespace Kahlan\Spec\Suite\Matcher;

use stdClass;
use Kahlan\Matcher\ToBeA;

describe("toBeA", function () {

    describe("::match()", function () {

        it("passes if true is a boolean", function () {

            expect(true)->toBeA('boolean');

        });

        it("passes if false is a boolean", function () {

            expect(false)->toBeA('boolean');

        });

        it("passes if true is a bool", function () {

            expect(true)->toBeA('bool');

        });

        it("passes if false is a bool", function () {

            expect(false)->toBeA('bool');

        });

        it("passes if 1 is an integer", function () {

            expect(1)->toBeA('integer');

        });

        it("passes if 1 is an int", function () {

            expect(1)->toBeA('int');

        });

        it("passes if 'Hello World' is a string", function () {

            expect('Hello World')->toBeA('string');

        });

        it("passes if [1, 3, 7] is an array", function () {

            expect([1, 3, 7])->toBeA('array');

        });

        it("passes if 1.5 is a float", function () {

            expect(1.5)->toBeA('float');

        });

        it("passes if an instance of stdClass is an object", function () {

            expect(new stdClass())->toBeA('object');

        });

        it("passes if null is NULL", function () {

            expect(null)->toBeA('null');

        });

        it("passes if a resource is a resource", function () {

            expect(opendir(sys_get_temp_dir()))->toBeA('resource');

        });

    });

    describe("::description()", function () {

        it("returns the description message", function () {

            ToBeA::match(1, 'boolean');
            $actual = ToBeA::description();

            expect($actual['description'])->toBe('have the expected type.');
            expect((string) $actual['data']['actual'])->toBe('integer');
            expect((string) $actual['data']['expected'])->toBe('boolean');

        });

    });

});
