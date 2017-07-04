<?php
namespace {
    use Utilities\MyOtherClass;

    class MyClass
    {
        public function __construct()
        {
            $this->test = new MyOtherClass;
        }
    }
}

namespace Test {
    use Some\Name\Space\MyOtherClass;

    class MyClass
    {
        public function __construct()
        {
            $instance = new MyOtherClass;
        }
    }
}
