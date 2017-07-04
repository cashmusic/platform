<?php
namespace Kahlan\Spec\Suite;

use Exception;
use stdClass;
use DateTime;
use SplMaxHeap;
use Kahlan\Arg;
use Kahlan\Matcher;
use Kahlan\Plugin\Double;

describe("Arg", function () {

    beforeEach(function () {
        $this->matchers = Matcher::get();
    });

    afterEach(function () {
        Matcher::reset();
        foreach ($this->matchers as $name => $value) {
            foreach ($value as $for => $class) {
                Matcher::register($name, $class, $for);
            }
        }
    });

    describe("::__callStatic()", function () {

        it("creates matcher", function () {

            $arg = Arg::toBe(true);
            expect($arg->match(true))->toBe(true);
            expect($arg->match(true))->not->toBe(false);

        });

        it("creates a negative matcher", function () {

            $arg = Arg::notToBe(true);
            expect($arg->match(true))->not->toBe(true);
            expect($arg->match(true))->toBe(false);

        });

        it("registers a matcher for a specific class", function () {

            Matcher::register('toEqualCustom', Double::classname(['extends' => 'Kahlan\Matcher\ToEqual']), 'stdClass');

            $arg = Arg::toEqualCustom(new stdClass());
            expect($arg->match(new stdClass()))->toBe(true);

            $arg = Arg::toEqualCustom(new DateTime());
            expect($arg->match(new stdClass()))->not->toBe(true);

        });

        it("makes registered matchers for a specific class available for sub classes", function () {

            Matcher::register('toEqualCustom', Double::classname(['extends' => 'Kahlan\Matcher\ToEqual']), 'SplHeap');

            $arg = Arg::toEqualCustom(new SplMaxHeap());
            expect($arg->match(new SplMaxHeap()))->toBe(true);

        });

        it("throws an exception using an undefined matcher name", function () {

            $closure = function () {
                $arg = Arg::toHelloWorld(true);
            };
            expect($closure)->toThrow(new Exception("Unexisting matchers attached to `'toHelloWorld'`."));

        });

        it("throws an exception using an matcher name which doesn't match actual", function () {

            Matcher::register('toEqualCustom', Double::classname(['extends' => 'Kahlan\Matcher\ToEqual']), 'SplHeap');

            $closure = function () {
                $arg = Arg::toEqualCustom(new SplMaxHeap());
                $arg->match(true);
            };
            expect($closure)->toThrow(new Exception("Unexisting matcher attached to `'toEqualCustom'` for `SplHeap`."));

        });

    });

});
