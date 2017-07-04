<?php
namespace Kahlan\Spec\Suite\Matcher;

use Error;
use Exception;
use RuntimeException;
use Kahlan\Matcher\ToThrow;

describe("toThrow", function () {

    describe("::match()", function () {

        it("catches any kind of exception", function () {

            $closure = function () {
                throw new RuntimeException();
            };
            expect($closure)->toThrow();

            $closure = function () {
                throw new Exception('exception message');
            };
            expect($closure)->toThrow();

        });

        it("catches any kind of exception but with a specific code", function () {

            $closure = function () {
                throw new RuntimeException('runtime error', 500);
            };
            expect($closure)->toThrow(null, 500);

            $closure = function () {
                throw new Exception('exception message', 500);
            };
            expect($closure)->toThrow(null, 500);

        });

        it("doesn't catches any kind of exception with a specific code", function () {

            $closure = function () {
                throw new Exception('exception message');
            };
            expect($closure)->not->toThrow(null, 400);

            $closure = function () {
                throw new Exception('exception message', 500);
            };
            expect($closure)->not->toThrow(null, 400);

        });

        it("catches a detailed exception", function () {

            $closure = function () {
                throw new RuntimeException('exception message');
            };
            expect($closure)->toThrow(new RuntimeException('exception message'));

        });

        it("catches a detailed exception with some specific code", function () {

            $closure = function () {
                throw new RuntimeException('exception message', 500);
            };
            expect($closure)->not->toThrow(new RuntimeException('exception message'));
            expect($closure)->toThrow(new RuntimeException('exception message', 500));

        });

        it("catches a detailed exception using the message name only", function () {

            $closure = function () {
                throw new RuntimeException('exception message');
            };
            expect($closure)->toThrow('exception message');

        });

        it("catches an exception message using a regular expression", function () {

            $closure = function () {
                throw new RuntimeException('exception stuff message');
            };
            expect($closure)->toThrow('/exception (.*?) message/');

            $closure = function () {
                throw new RuntimeException('exception stuff message');
            };
            expect($closure)->toThrow('~exception (.*?) message~');

            $closure = function () {
                throw new RuntimeException('exception stuff message');
            };
            expect($closure)->toThrow('#exception (.*?) message#');

            $closure = function () {
                throw new RuntimeException('exception stuff message');
            };
            expect($closure)->toThrow('@exception (.*?) message@');

            $closure = function () {
                throw new RuntimeException('exception stuff message');
            };
            expect($closure)->not->toThrow('@exception (.*?) message#');

        });

        it("catches any kind of error", function () {

            skipIf(defined('HHVM_VERSION') || PHP_MAJOR_VERSION < 7);

            $closure = function () {
                throw new Error();
            };
            expect($closure)->toThrow();

        });

        it("doesn't catch not an exception", function () {
            $closure = function () {
                return true;
            };

            expect($closure)->not->toThrow(new Exception());
        });

        it("doesn't catch whatever exception if a detailed one is expected", function () {

            $closure = function () {
                throw new RuntimeException();
            };
            expect($closure)->not->toThrow(new RuntimeException('exception message'));

        });

        it("doesn't catch the exception if the expected exception has a different class name", function () {

            $closure = function () {
                throw new Exception('exception message');
            };
            expect($closure)->not->toThrow(new RuntimeException('exception message'));

            $closure = function () {
                throw new RuntimeException('exception message');
            };
            expect($closure)->not->toThrow(new Exception('exception message'));

        });

    });

    describe("::description()", function () {

        it("returns the description message", function () {

            $actualException = new Exception();
            $expectedException = new Exception();

            $actual = function () use ($actualException) {
                throw $actualException;
            };

            ToThrow::match($actual, $expectedException, 0);
            $actual = ToThrow::description();

            expect($actual['description'])->toBe('throw a compatible exception.');
            expect($actual['data']['actual'])->toBe($actualException);
            expect($actual['data']['expected'])->toBe($expectedException);

        });

        it("returns the description message when actual doesn't throw any exception", function () {

            $exception = new Exception();

            ToThrow::match(function () {}, $exception, 0);
            $actual = ToThrow::description();

            expect($actual['description'])->toBe('throw a compatible exception.');
            expect($actual['data']['actual'])->toBe(null);
            expect($actual['data']['expected'])->toBe($exception);

        });

        it("returns the description message when the expected value is a string", function () {

            ToThrow::match(function () {}, 'Expected exception message', 0);
            $actual = ToThrow::description();

            expect($actual['description'])->toBe('throw a compatible exception.');
            expect($actual['data']['actual'])->toBe(null);
            expect($actual['data']['expected'])->toBeAnInstanceOf('Kahlan\Matcher\AnyException');
            expect($actual['data']['expected']->getMessage())->toBe('Expected exception message');

        });

    });

});
