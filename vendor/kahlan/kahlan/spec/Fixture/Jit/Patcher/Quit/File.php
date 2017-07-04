<?php
namespace Kahlan\Spec\Fixture\Jit\Patcher\Quit;

class Example
{
    public function exitStatement()
    {
        exit(-1);
    }

    public function exitStatementShortSyntax()
    {
        exit;
    }

    public function dieStatement()
    {
        die();
    }

    public function dieStatementShortSyntax()
    {
        die();
    }

    public function normalStatement()
    {
        fooexit();
        $instance->exit();
        $exit = 'exit();';
    }
}
