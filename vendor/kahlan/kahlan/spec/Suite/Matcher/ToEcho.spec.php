<?php
namespace Kahlan\Spec\Suite\Matcher;

use Kahlan\Matcher\ToEcho;

describe("toEcho", function () {

    describe("::match()", function () {

        it("passes if `'Hello World!'` is echoed", function () {

            expect(function () {
                echo 'Hello World!';
            })->toEcho('Hello World!');

        });

        it("passes if `'Hello World'` is not echoed", function () {

            expect(function () {
                echo 'Good Bye!';
            })->not->toEcho('Hello World!');

        });

    });

    describe("::description()", function () {

        it("returns the description message", function () {

            ToEcho::match(function () {
                echo 'Hello';
            }, 'Good Bye!');
            $actual = ToEcho::description();

            expect($actual)->toBe([
                'description' => 'echo the expected string.',
                'data'        => [
                    "actual"   => "Hello",
                    "expected" => "Good Bye!"
                ]
            ]);

        });

    });

});
