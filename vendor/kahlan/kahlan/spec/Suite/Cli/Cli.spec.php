<?php
namespace Kahlan\Spec\Suite\Cli;

use Kahlan\Cli\Cli;

describe("Cli", function () {

    describe('->color()', function () {

        beforeAll(function () {
            $this->check = function ($actual, $expected) {
                expect(strlen($actual))->toBe(strlen($expected));

                for ($i=0; $i < strlen($actual); $i++) {
                    $check = (ord($actual[$i]) == ord($expected[$i])) ? true : false;
                    if ($check) {
                        break;
                    }
                }

                expect($check)->toBe(true);
            };
        });

        it("leaves string unchanged whith no options", function () {

            expect(Cli::color("String"))->toBe("String");

        });

        it("applies a color using a string as options", function () {

            $this->check(Cli::color("String", "yellow"), "\e[0;33;49mSrting\e[0m");

        });

        it("applies a complex color using a semicolon separated string as options", function () {

            $this->check(Cli::color("String", "n;yellow;100"), "\e[0;33;110mSrting\e[0m");
            $this->check(Cli::color("String", "4;red;100"), "\e[0;31;110mSrting\e[0m");
            $this->check(Cli::color("String", "n;100;100"), "\e[0;100;100mSrting\e[0m");

        });

        it("applies the 39 default color with unknown color name", function () {

            $this->check(Cli::color("String", "some_strange_color"), "\e[0;39;49mSrting\e[0m");

        });

    });

    describe('->bell()', function () {

        it("bells", function () {

            expect(function () {
                Cli::bell(2);
            })->toEcho(str_repeat("\007", 2));

        });

    });

});
