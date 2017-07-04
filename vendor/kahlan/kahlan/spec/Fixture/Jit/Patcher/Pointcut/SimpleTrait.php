<?php
namespace Kahlan\Spec\Fixture\Jit\Patcher\Pointcut;

use Kahlan\MongoId;

trait SimpleTrait
{
    protected $_variable = true;

    public function __construct($options)
    {
    }

    function classicMethod($param1, &$param2, &$param2 = [])
    {
        rand(2, 5);
    }

    public function publicMethod($param1, &$param2, &$param2 = [])
    {
        rand(2, 5);
    }

    protected function protectedMethod($param1, &$param2, &$param2 = [])
    {
        rand(2, 5);
    }

    private function privateMethod($param1, &$param2, &$param2 = [])
    {
        rand(2, 5);
    }

    final public function finalMethod($param1 = 'default', $param2 = null)
    {
        rand(2, 5);
    }

    abstract public function abstractMethod($param1, &$param2 = array());

}
