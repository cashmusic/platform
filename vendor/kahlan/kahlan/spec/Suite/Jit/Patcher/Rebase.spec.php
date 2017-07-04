<?php
namespace Kahlan\Spec\Suite\Jit\Patcher;

use Kahlan\Jit\Parser;
use Kahlan\Jit\Patcher\Rebase;

describe("Rebase", function () {

    beforeEach(function () {
        $this->path = 'spec/Fixture/Jit/Patcher/Rebase';
        $this->patcher = new Rebase();
    });

    describe("->process()", function () {

        it("patches class's methods", function () {

            $nodes = Parser::parse(file_get_contents($this->path . '/Rebase.php'));
            $expected = file_get_contents($this->path . '/RebaseProcessed.php');
            $actual = Parser::unparse($this->patcher->process($nodes, '/the/original/path/Rebase.php'));
            expect($actual)->toBe($expected);

        });

    });

    describe("->patchable()", function () {

        it("return `true`", function () {

            expect($this->patcher->patchable('SomeClass'))->toBe(true);

        });

    });
});
