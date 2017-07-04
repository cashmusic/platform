<?php
namespace Kahlan\Spec\Fixture\Jit\Patcher\Monkey;

use Kahlan\Util\Text;

trait Filterable
{
    protected function dump()
    {
        return Text::dump('Hello');
    }

}
