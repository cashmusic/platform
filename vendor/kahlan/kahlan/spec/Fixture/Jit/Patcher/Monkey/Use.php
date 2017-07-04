<?php
namespace Kahlan\Spec\Fixture\Jit\Patcher\Monkey;

use My\Name\Space\MyClass as MyAlias;

use function My\Name\Space\functionName1;
use function My\Name\Space\functionName2 as func;

use const My\Name\Space\CONSTANT;

$instance = new MyAlias;

functionName1(0);
func();
echo CONSTANT;
