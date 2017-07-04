<?php
namespace Kahlan\Spec\Fixture\Plugin\Double;

class Doz
{
    public function foo($a)
    {
    }
    public function foo2($b = null)
    {
    }
    public function foo3(array $b = array())
    {
    }
    public function foo4(callable $fct)
    {
    }
    public function foo5(\Closure $fct)
    {
    }
    public function foo6(\Exception $e)
    {
    }
    public function foo7(DozInterface $instance)
    {
    }
    final public function foo8()
    {
    }
}
