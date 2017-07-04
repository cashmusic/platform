<?php
namespace Kahlan\Spec\Fixture\Jit\Patcher\Quit;

class Example
{
    public function exitStatement()
    {
        \Kahlan\Plugin\Quit::quit(-1);
    }

    public function exitStatementShortSyntax()
    {
        \Kahlan\Plugin\Quit::quit();
    }

    public function dieStatement()
    {
        \Kahlan\Plugin\Quit::quit();
    }

    public function dieStatementShortSyntax()
    {
        \Kahlan\Plugin\Quit::quit();
    }

    public function normalStatement()
    {
        fooexit();
        $instance->exit();
        $exit = 'exit();';
    }
}
