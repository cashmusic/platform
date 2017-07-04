<?php
namespace code\spec\suite;

use Exception;
use InvalidArgumentException;
use Kahlan\Code\TimeoutException;
use Kahlan\Code\Code;

describe("Code", function () {

    declare(ticks = 1) {

        describe("::run()", function () {

            beforeEach(function () {
                if (!function_exists('pcntl_signal')) {
                    skipIf(true);
                }
            });

            it("runs the passed closure", function () {

                $start = microtime(true);

                expect(Code::run(function () {
                    return true;
                }, 1))->toBe(true);

                $end = microtime(true);
                expect($end - $start)->toBeLessThan(1);

            });

            it("throws an exception if an invalid closure is provided", function () {

                $closure = function () {
                    Code::run("invalid", 1);
                };

                expect($closure)->toThrow(new InvalidArgumentException());

            });

            it("throws an exception on timeout", function () {

                $start = microtime(true);

                $closure = function () {
                    Code::run(function () {
                        while (true) {
                            sleep(1);
                        }
                    }, 1);
                };

                expect($closure)->toThrow(new TimeoutException('Timeout reached, execution aborted after 1 second(s).'));

                $end = microtime(true);
                expect($end - $start)->toBeGreaterThan(1);

            });

            it("throws all unexpected exceptions", function () {

                $closure = function () {
                    Code::run(function () {
                        throw new Exception("Error Processing Request");
                    }, 1);
                };

                expect($closure)->toThrow(new Exception("Error Processing Request"));

            });

        });

    }

    describe("::spin()", function () {

        it("runs the passed closure", function () {

            $start = microtime(true);

            expect(Code::spin(function () {
                return true;
            }, 1))->toBe(true);

            $end = microtime(true);
            expect($end - $start)->toBeLessThan(1);

        });

        it("throws an exception if an invalid closure is provided", function () {

            $closure = function () {
                Code::spin("invalid", 1);
            };

            expect($closure)->toThrow(new InvalidArgumentException());

        });

        it("throws an exception on timeout", function () {

            $start = microtime(true);

            $closure = function () {
                Code::spin(function () {}, 1);
            };

            expect($closure)->toThrow(new TimeoutException('Timeout reached, execution aborted after 1 second(s).'));

            $end = microtime(true);
            expect($end - $start)->toBeGreaterThan(1);

        });

        it("respects the delay delay", function () {

            $start = microtime(true);

            $counter = 0;
            $closure = function () use (&$counter) {
                Code::spin(function () use (&$counter) {
                    $counter++;
                }, 1, 250000);
            };

            expect($closure)->toThrow(new TimeoutException('Timeout reached, execution aborted after 1 second(s).'));
            expect($counter)->toBeGreaterThan(3);
            expect($counter)->toBeLessThan(6);

            $end = microtime(true);
            expect($end - $start)->toBeGreaterThan(1);

        });

    });

});
