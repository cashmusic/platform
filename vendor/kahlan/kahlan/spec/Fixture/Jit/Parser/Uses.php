<?php

use Kahlan\A;
use Kahlan\B, Kahlan\C;
use Kahlan\E as F;
use Kahlan\E as G;
use stdClass as StandardClass;
use Foo\Bar\Baz\{ ClassA, ClassB, Fuz\ClassC as ClassD };
use function My\Name\Space\functionName1;
use function My\Name\Space\functionName2 as func;
use const My\Name\Space\CONSTANT;

$fct = function () use ($a, $b) {
    return $a + $b;
}
