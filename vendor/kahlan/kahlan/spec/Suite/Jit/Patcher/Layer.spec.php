<?php
namespace Kahlan\Spec\Suite\Jit\Patcher;

use Kahlan\Jit\Parser;
use Kahlan\Jit\Patcher\Layer;

describe("Layer", function () {

    beforeEach(function () {
        $this->path = 'spec/Fixture/Jit/Patcher/Layer';
        $this->patcher = new Layer([
            'override' => [
                'Kahlan\Analysis\Inspector'
            ]
        ]);
    });

    describe("->findFile()", function () {

        it("returns the file path to patch", function () {

            $layer = new Layer();
            expect($layer->findFile(null, null, '/some/file/path.php'))->toBe('/some/file/path.php');

        });

    });

    describe("->process()", function () {

        it("patches class's extends", function () {

            $nodes = Parser::parse(file_get_contents($this->path . '/Layer.php'));
            $actual = Parser::unparse($this->patcher->process($nodes));

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Fixture\\Jit\\Patcher\\Layer;

class Inspector extends InspectorKLAYER
{

}class InspectorKLAYER extends \\Kahlan\\Analysis\\Inspector {    public static function inspect(\$class) {\$__KPOINTCUT_ARGS__ = func_get_args(); \$__KPOINTCUT_SELF__ = isset(\$this) ? \$this : get_called_class(); if (\$__KPOINTCUT__ = \\Kahlan\\Plugin\\Pointcut::before(__METHOD__, \$__KPOINTCUT_SELF__, \$__KPOINTCUT_ARGS__)) { \$r = \$__KPOINTCUT__(\$__KPOINTCUT_ARGS__, \$__KPOINTCUT_SELF__); return \$r; }return parent::inspect(\$class);}    public static function parameters(\$class, \$method, \$data = NULL) {\$__KPOINTCUT_ARGS__ = func_get_args(); \$__KPOINTCUT_SELF__ = isset(\$this) ? \$this : get_called_class(); if (\$__KPOINTCUT__ = \\Kahlan\\Plugin\\Pointcut::before(__METHOD__, \$__KPOINTCUT_SELF__, \$__KPOINTCUT_ARGS__)) { \$r = \$__KPOINTCUT__(\$__KPOINTCUT_ARGS__, \$__KPOINTCUT_SELF__); return \$r; }return parent::parameters(\$class, \$method, \$data);}    public static function typehint(\$parameter) {\$__KPOINTCUT_ARGS__ = func_get_args(); \$__KPOINTCUT_SELF__ = isset(\$this) ? \$this : get_called_class(); if (\$__KPOINTCUT__ = \\Kahlan\\Plugin\\Pointcut::before(__METHOD__, \$__KPOINTCUT_SELF__, \$__KPOINTCUT_ARGS__)) { \$r = \$__KPOINTCUT__(\$__KPOINTCUT_ARGS__, \$__KPOINTCUT_SELF__); return \$r; }return parent::typehint(\$parameter);}}

EOD;

            expect($actual)->toBe($expected);

        });

        it("bails out when `'override'` is empty", function () {

            $this->patcher = new Layer([]);
            $nodes = Parser::parse(file_get_contents($this->path . '/Layer.php'));
            $actual = Parser::unparse($this->patcher->process($nodes));

            expect($actual)->toBe("");

        });

        it("doesn't patch classes which are not present in the `'override'` option", function () {

            $this->patcher = new Layer([
                'override' => [
                    'Kahlan\Analysis\Debugger'
                ]
            ]);

            $nodes = Parser::parse(file_get_contents($this->path . '/Layer.php'));
            $actual = Parser::unparse($this->patcher->process($nodes));
            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Fixture\\Jit\\Patcher\\Layer;

class Inspector extends \\Kahlan\\Analysis\\Inspector
{

}

EOD;
            expect($actual)->toBe($expected);

        });

    });

    describe("->patchable()", function () {

        it("return `true`", function () {

            expect($this->patcher->patchable('SomeClass'))->toBe(true);

        });

    });

});
