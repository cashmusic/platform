<?php
namespace Kahlan\Spec\Suite;

use Exception;
use RuntimeException;
use stdClass;
use DateTime;
use Kahlan\Specification;
use Kahlan\Matcher;
use Kahlan\Expectation;
use Kahlan\Plugin\Double;

function expectation($actual, $timeout = -1)
{
    return new Expectation(compact('actual', 'timeout'));
}

describe("Expectation", function () {

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

    describe("->__call()", function () {

        it("throws an exception when using an undefined matcher name", function () {

            $closure = function () {
                $result = expectation(true)->toHelloWorld(true);
            };

            expect($closure)->toThrow(new Exception("Unexisting matcher attached to `'toHelloWorld'`."));

        });

        it("throws an exception when a specific class matcher doesn't match", function () {

            Matcher::register('toEqualCustom', Double::classname(['extends' => 'Kahlan\Matcher\ToEqual']), 'stdClass');

            $closure = function () {
                $result = expectation([])->toEqualCustom(new stdClass());
            };

            expect($closure)->toThrow(new Exception("Unexisting matcher attached to `'toEqualCustom'` for `stdClass`."));

        });

        it("doesn't wait when the spec passes", function () {

            $start = microtime(true);
            $result = expectation(true, 1)->toBe(true);
            $end = microtime(true);
            expect($end - $start)->toBeLessThan(1);

        });

        it("loops until the timeout is reached on failure", function () {

            $start = microtime(true);
            $result = expectation(true, 0.1)->toBe(false);
            $end = microtime(true);
            expect($end - $start)->toBeGreaterThan(0.1);
            expect($end - $start)->toBeLessThan(0.2);

        });

        it("loops until the timeout is reached on failure using a sub spec with a return value", function () {

            $start = microtime(true);
            $subspec = new Specification(['closure' => function () {
                return true;
            }]);
            $result = expectation($subspec, 0.1)->toBe(false);
            $end = microtime(true);
            expect($end - $start)->toBeGreaterThan(0.1);
            expect($end - $start)->toBeLessThan(0.2);

        });

        it("doesn't wait on failure when a negative expectation is expected", function () {

            $start = microtime(true);
            $result = expectation(true, 1)->not->toBe(false);
            $end = microtime(true);
            expect($end - $start)->toBeLessThan(1);

        });

    });

    describe("->passed()", function () {

        it("verifies the expectation", function () {

            $actual = expectation(true)->toBe(true)->passed();
            expect($actual)->toBe(true);

        });

        it("verifies nested expectations inside a spec", function () {

            $spec = new Specification(['closure' => function () {
                return true;
            }]);
            $actual = expectation($spec)->toBe(true)->passed();
            expect($actual)->toBe(true);

        });

        it("loops until the timeout is reached on failure", function () {

            $start = microtime(true);
            $spec = new Specification(['closure' => function () {
                expect(true)->toBe(false);
            }]);
            $actual = expectation($spec, 0.1)->passed();
            expect($actual)->toBe(false);
            $end = microtime(true);
            expect($end - $start)->toBeGreaterThan(0.1);
            expect($end - $start)->toBeLessThan(0.2);

        });

    });

    describe("->__get()", function () {

        it("sets the not value using `'not'`", function () {

            $expectation = new Expectation();
            expect($expectation->not())->toBe(false);
            expect($expectation->not)->toBe($expectation);
            expect($expectation->not())->toBe(true);

        });

        it("throws an exception with unsupported attributes", function () {

            $closure = function () {
                $expectation = new Expectation();
                $expectation->abc;
            };
            expect($closure)->toThrow(new Exception('Unsupported attribute `abc`.'));

        });

    });

    describe("->clear()", function () {

        it("clears an expectation", function () {

            $actual = Double::instance();
            $expectation = expectation($actual, 10);
            $matcher = $expectation->not->toReceive('helloWorld');

            expect($expectation->actual())->toBe($actual);
            expect($expectation->deferred())->toBe([
                'matcherName' => 'toReceive',
                'matcher' => 'Kahlan\Matcher\ToReceive',
                'data' => [
                    'actual' => $actual,
                    'expected' => 'helloWorld'
                ],
                'instance' => $matcher,
                'not' => true
            ]);
            expect($expectation->timeout())->toBe(10);
            expect($expectation->not())->toBe(true);
            expect($expectation->passed())->toBe(true);
            expect($expectation->logs())->toHaveLength(1);

            $expectation->clear();

            expect($expectation->actual())->toBe(null);
            expect($expectation->deferred())->toBe(null);
            expect($expectation->timeout())->toBe(-1);
            expect($expectation->not())->toBe(false);
            expect($expectation->passed())->toBe(true);
            expect($expectation->logs())->toHaveLength(0);

        });

    });

});
