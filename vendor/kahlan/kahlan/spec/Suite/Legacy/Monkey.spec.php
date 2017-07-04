<?php
namespace Kahlan\Spec\Suite;

use Exception;
use Kahlan\Suite;
use Kahlan\Jit\Parser;
use Kahlan\Jit\Patcher\Monkey;

describe("Monkey", function () {

    beforeAll(function () {
        Suite::$PHP = 5;
    });

    afterAll(function () {
        Suite::$PHP = PHP_MAJOR_VERSION;
    });

    beforeEach(function () {
        $this->path = 'spec/Fixture/Jit/Patcher/Monkey';
        $this->patcher = new Monkey();
    });

    describe("->process()", function () {

        it("patches class's methods", function () {

            $nodes = Parser::parse(file_get_contents($this->path . '/Class.php'));
            $expected = file_get_contents($this->path . '/ClassProcessedLegacy.php');
            $actual = Parser::unparse($this->patcher->process($nodes));
            expect($actual)->toBe($expected);

        });

        it("patches plain php file", function () {

            $nodes = Parser::parse(file_get_contents($this->path . '/Plain.php'));
            $expected = file_get_contents($this->path . '/PlainProcessedLegacy.php');
            $actual = Parser::unparse($this->patcher->process($nodes));
            expect($actual)->toBe($expected);

        });
    });
});
