<?php
namespace Kahlan\Spec\Fixture\Jit\Patcher\Reabase;

class Example
{
    public function load()
    {
        require '/the/original/path/Rebase.php';
    }

    public function filename()
    {
        return basename('/the/original/path/Rebase.php');
    }

    public function path()
    {
        return '/the/original/path';
    }
}
