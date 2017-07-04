<?php
namespace Kahlan\Spec\Fixture\Plugin\Pointcut;

class SubBar extends Bar
{
    use \Kahlan\Spec\Fixture\Plugin\Pointcut\SubTrait;

    public function overrided()
    {
        return 'SubBar';
    }
}
