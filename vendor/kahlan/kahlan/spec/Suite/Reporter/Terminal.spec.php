<?php
namespace Kahlan\Spec\Suite\Reporter\Coverage;

use Kahlan\Reporter\Terminal;

describe("Terminal", function () {

    beforeEach(function () {
        $this->terminal = new Terminal([]);
    });

    describe("->kahlan()", function () {

        it("returns the kahlan ascii logo", function () {
            $kahlan = <<<EOD
            _     _
  /\ /\__ _| |__ | | __ _ _ __
 / //_/ _` | '_ \| |/ _` | '_ \
/ __ \ (_| | | | | | (_| | | | |
\/  \/\__,_|_| |_|_|\__,_|_| |_|
EOD;
            $actual = $this->terminal->kahlan();
            expect($actual)->toBe($kahlan);
        });

    });

    describe("->kahlanBaseline()", function () {

        it("returns the baseline", function () {

            $actual = $this->terminal->kahlanBaseline();
            expect($actual)->toBe("The PHP Test Framework for Freedom, Truth and Justice.");

        });

    });

    describe("->indent()", function () {

        it("returns no indentation by default", function () {

            $actual = $this->terminal->indent();
            expect($actual)->toBe(0);

        });

        it("returns indent", function () {

            $indent = 2;
            $actual = $this->terminal->indent($indent);
            expect($actual)->toBe($indent);

        });

    });

    describe("->prefix()", function () {

        it("returns an empty prefix by default", function () {

            $actual = $this->terminal->prefix();
            expect($actual)->toBe('');

        });

        it("sets the prefix string", function () {

            $prefix = 'prefix';
            $actual = $this->terminal->prefix($prefix);
            expect($actual)->toBe($prefix);
        });

    });

    describe("->readableSize()", function () {

        it("returns `'0'` when size is < `1`", function () {

            $readableSize = '0';
            $actual = $this->terminal->readableSize(0, 2, 1024);
            expect($actual)->toBe($readableSize);

        });

        it("doesn't add any unit for small size number", function () {

            $readableSize = '10';
            $actual = $this->terminal->readableSize(10, 2, 1024);
            expect($actual)->toBe($readableSize);

        });

        it("formats big size number using appropriate unit", function () {

            $readableSize = '9.77K';
            $actual = $this->terminal->readableSize(10000, 2, 1024);
            expect($actual)->toBe($readableSize);

        });

    });

});
