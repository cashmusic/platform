<?php
namespace Kahlan\Spec\Fixture\Plugin\Pointcut;

class Bar
{
    public function send()
    {
        return 'success';
    }

    public static function sendStatic()
    {
        return 'static success';
    }

    public function overrided()
    {
        return 'Bar';
    }
}
