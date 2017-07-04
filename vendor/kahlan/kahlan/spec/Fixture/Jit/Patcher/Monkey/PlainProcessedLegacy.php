<?php $__KMONKEY__0=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'function_exists');$__KMONKEY__1=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'mt_rand');$__KMONKEY__2__=null;$__KMONKEY__2=\Kahlan\Plugin\Monkey::patched(null, 'name\space\MyClass', false, $__KMONKEY__2__);$__KMONKEY__3=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'time');$__KMONKEY__4__=null;$__KMONKEY__4=\Kahlan\Plugin\Monkey::patched(null, 'name\space\MyClass2', false, $__KMONKEY__4__); ?><?php
use name\space\MyClass as MyAlias;
use name\space as space;

if ($__KMONKEY__0('myfunction')) {
    $thatIsWeird = true;
}

$rand = $__KMONKEY__1();
new $__KMONKEY__2;
new $__KMONKEY__4($__KMONKEY__3());
