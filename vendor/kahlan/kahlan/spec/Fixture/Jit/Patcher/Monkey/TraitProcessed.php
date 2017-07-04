<?php
namespace Kahlan\Spec\Fixture\Jit\Patcher\Monkey;

use Kahlan\Util\Text;

trait Filterable
{
    protected function dump()
    {$__KMONKEY__0__=null;$__KMONKEY__0=\Kahlan\Plugin\Monkey::patched(null, 'Kahlan\Util\Text', false, $__KMONKEY__0__);
        return $__KMONKEY__0::dump('Hello');
    }

}
