<?php
namespace Kahlan;

use Kahlan\Box\Box;
use Kahlan\Box\BoxException;
use Kahlan\Expectation;
use Kahlan\Suite;
use Kahlan\Specification;
use Kahlan\Allow;

function beforeAll($closure)
{
    return Suite::current()->beforeAll($closure);
}

function afterAll($closure)
{
    return Suite::current()->afterAll($closure);
}

function beforeEach($closure)
{
    return Suite::current()->beforeEach($closure);
}

function afterEach($closure)
{
    return Suite::current()->afterEach($closure);
}

function describe($message, $closure, $timeout = null, $type = 'normal')
{
    if (!Suite::current()) {
        $suite = box('kahlan')->get('suite.global');
        return $suite->describe($message, $closure, $timeout, $type);
    }
    return Suite::current()->describe($message, $closure, $timeout, $type);
}

function context($message, $closure, $timeout = null, $type = 'normal')
{
    return Suite::current()->context($message, $closure, $timeout, $type);
}

function given($name, $value)
{
    return Suite::current()->given($name, $value);
}

function it($message, $closure = null, $timeout = null, $type = 'normal')
{
    return Suite::current()->it($message, $closure, $timeout, $type);
}

function fdescribe($message, $closure, $timeout = null)
{
    return describe($message, $closure, $timeout, 'focus');
}

function fcontext($message, $closure, $timeout = null)
{
    return context($message, $closure, $timeout, 'focus');
}

function fit($message, $closure = null, $timeout = null)
{
    return it($message, $closure, $timeout, 'focus');
}

function xdescribe($message, $closure, $timeout = null)
{
    return describe($message, $closure, $timeout, 'exclude');
}

function xcontext($message, $closure, $timeout = null)
{
    return context($message, $closure, $timeout, 'exclude');
}

function xit($message, $closure = null, $timeout = null)
{
    return it($message, $closure, $timeout, 'exclude');
}

function waitsFor($actual, $timeout = null)
{
    return Specification::current()->waitsFor($actual, $timeout);
}

function skipIf($condition)
{
    $current = Specification::current() ?: Suite::current();
    return $current->skipIf($condition);
}

/**
 * @param $actual
 *
 * @return Expectation
 */
function expect($actual)
{
    return Specification::current()->expect($actual);
}

/**
 * @param $actual
 *
 * @return Allow
 */
function allow($actual)
{
    return new Allow($actual);
}

function box($name = '', $box = null)
{
    static $boxes = [];

    if (func_num_args() === 1) {
        if ($name === false) {
            $boxes = [];
            return;
        }
        if (is_object($name)) {
            return $boxes[''] = $name;
        }
        if (isset($boxes[$name])) {
            return $boxes[$name];
        }
        throw new BoxException("Unexisting box `'{$name}'`.");
    }
    if (func_num_args() === 2) {
        if ($box === false) {
            unset($boxes[$name]);
            return;
        }
        return $boxes[$name] = $box;
    }
    if (!isset($boxes[''])) {
        $boxes[''] = new Box();
    }
    return $boxes[''];
}
