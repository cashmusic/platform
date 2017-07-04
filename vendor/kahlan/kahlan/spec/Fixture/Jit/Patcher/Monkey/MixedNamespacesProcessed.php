<?php
namespace {
    use Utilities\MyOtherClass;

    class MyClass
    {
        public function __construct()
        {$__KMONKEY__0__=null;$__KMONKEY__0=\Kahlan\Plugin\Monkey::patched(null, 'Utilities\MyOtherClass', false, $__KMONKEY__0__);
            $this->test = ($__KMONKEY__0__?$__KMONKEY__0__:new $__KMONKEY__0);
        }
    }
}

namespace Test {
    use Some\Name\Space\MyOtherClass;

    class MyClass
    {
        public function __construct()
        {$__KMONKEY__1__=null;$__KMONKEY__1=\Kahlan\Plugin\Monkey::patched(null, 'Some\Name\Space\MyOtherClass', false, $__KMONKEY__1__);
            $instance = ($__KMONKEY__1__?$__KMONKEY__1__:new $__KMONKEY__1);
        }
    }
}
