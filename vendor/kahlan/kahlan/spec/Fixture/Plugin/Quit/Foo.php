<?php
namespace Kahlan\Spec\Fixture\Plugin\Quit;

class Foo
{
    public function exitStatement($status = 0)
    {
        exit($status);
    }

    public function dieStatement($status = 0)
    {
        die($status);
    }
}
