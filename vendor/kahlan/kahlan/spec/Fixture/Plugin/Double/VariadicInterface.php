<?php
namespace Kahlan\Spec\Fixture\Plugin\Double;

interface VariadicInterface
{
    public function foo(int ...$integers) : int;
}
