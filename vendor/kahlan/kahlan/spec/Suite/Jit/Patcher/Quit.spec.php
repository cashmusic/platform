<?php
namespace Kahlan\Spec\Suite\Jit\Patcher;

use Kahlan\Jit\Parser;
use Kahlan\Jit\Patcher\Quit;

describe("Quit", function () {

    beforeEach(function () {
        $this->path = 'spec/Fixture/Jit/Patcher/Quit';
        $this->patcher = new Quit();
    });

    describe("->process()", function () {

        it("patches exit/die statements", function () {

            $nodes = Parser::parse(file_get_contents($this->path . '/File.php'));
            $expected = file_get_contents($this->path . '/FileProcessed.php');
            $actual = Parser::unparse($this->patcher->process($nodes));
            expect($actual)->toBe($expected);

        });

    });

    describe("->patchable()", function () {

        it("return `true`", function () {

            expect($this->patcher->patchable('SomeClass'))->toBe(true);

        });

    });
});
