<?php
namespace Kahlan\Spec\Fixture\Jit\Patcher\Monkey;$__KMONKEY__0__=null;$__KMONKEY__0=\Kahlan\Plugin\Monkey::patched(null, 'My\Name\Space\MyClass', false, $__KMONKEY__0__);$__KMONKEY__1=\Kahlan\Plugin\Monkey::patched(null, 'My\Name\Space\functionName1');$__KMONKEY__2=\Kahlan\Plugin\Monkey::patched(null, 'My\Name\Space\functionName2');

use My\Name\Space\MyClass as MyAlias;

use function My\Name\Space\functionName1;
use function My\Name\Space\functionName2 as func;

use const My\Name\Space\CONSTANT;

$instance = ($__KMONKEY__0__?$__KMONKEY__0__:new $__KMONKEY__0);

$__KMONKEY__1(0);
$__KMONKEY__2();
echo CONSTANT;
