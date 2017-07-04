<?php
namespace Kahlan\Spec\Fixture\Jit\Patcher\Pointcut;

use Kahlan\MongoId;

class Simple extends \Kahlan\Fixture\Parent
{
    protected $_classes = [
        'bar' => 'Kahlan\Spec\Fixture\Plugin\Pointcut\Bar'
    ];

    protected $_status = 'none';

    protected $_message = 'Hello World!';

    protected static $_messageStatic = 'Hello Static World!';

    protected $_variable = true;

    public function __construct($options)
    {$__KPOINTCUT_ARGS__ = func_get_args(); $__KPOINTCUT_SELF__ = isset($this) ? $this : get_called_class(); if ($__KPOINTCUT__ = \Kahlan\Plugin\Pointcut::before(__METHOD__, $__KPOINTCUT_SELF__, $__KPOINTCUT_ARGS__)) { $r = $__KPOINTCUT__($__KPOINTCUT_ARGS__, $__KPOINTCUT_SELF__); return $r; }
    }

    function classicMethod($param1, &$param2, &$param2 = [])
    {$__KPOINTCUT_ARGS__ = func_get_args(); $__KPOINTCUT_SELF__ = isset($this) ? $this : get_called_class(); if ($__KPOINTCUT__ = \Kahlan\Plugin\Pointcut::before(__METHOD__, $__KPOINTCUT_SELF__, $__KPOINTCUT_ARGS__)) { $r = $__KPOINTCUT__($__KPOINTCUT_ARGS__, $__KPOINTCUT_SELF__); return $r; }
        rand(2, 5);
    }

    public function publicMethod($param1, &$param2, &$param2 = [])
    {$__KPOINTCUT_ARGS__ = func_get_args(); $__KPOINTCUT_SELF__ = isset($this) ? $this : get_called_class(); if ($__KPOINTCUT__ = \Kahlan\Plugin\Pointcut::before(__METHOD__, $__KPOINTCUT_SELF__, $__KPOINTCUT_ARGS__)) { $r = $__KPOINTCUT__($__KPOINTCUT_ARGS__, $__KPOINTCUT_SELF__); return $r; }
        rand(2, 5);
    }

    protected function protectedMethod($param1, &$param2, &$param2 = [])
    {$__KPOINTCUT_ARGS__ = func_get_args(); $__KPOINTCUT_SELF__ = isset($this) ? $this : get_called_class(); if ($__KPOINTCUT__ = \Kahlan\Plugin\Pointcut::before(__METHOD__, $__KPOINTCUT_SELF__, $__KPOINTCUT_ARGS__)) { $r = $__KPOINTCUT__($__KPOINTCUT_ARGS__, $__KPOINTCUT_SELF__); return $r; }
        rand(2, 5);
    }

    private function privateMethod($param1, &$param2, &$param2 = [])
    {$__KPOINTCUT_ARGS__ = func_get_args(); $__KPOINTCUT_SELF__ = isset($this) ? $this : get_called_class(); if ($__KPOINTCUT__ = \Kahlan\Plugin\Pointcut::before(__METHOD__, $__KPOINTCUT_SELF__, $__KPOINTCUT_ARGS__)) { $r = $__KPOINTCUT__($__KPOINTCUT_ARGS__, $__KPOINTCUT_SELF__); return $r; }
        rand(2, 5);
    }

    final public function finalMethod($param1 = 'default', $param2 = null)
    {$__KPOINTCUT_ARGS__ = func_get_args(); $__KPOINTCUT_SELF__ = isset($this) ? $this : get_called_class(); if ($__KPOINTCUT__ = \Kahlan\Plugin\Pointcut::before(__METHOD__, $__KPOINTCUT_SELF__, $__KPOINTCUT_ARGS__)) { $r = $__KPOINTCUT__($__KPOINTCUT_ARGS__, $__KPOINTCUT_SELF__); return $r; }
        rand(2, 5);
    }

    abstract public function abstractMethod($param1, &$param2 = array());

    public function generatorMethod($param1, &$param2, &$param2 = [])
    {$__KPOINTCUT_ARGS__ = func_get_args(); $__KPOINTCUT_SELF__ = isset($this) ? $this : get_called_class(); if ($__KPOINTCUT__ = \Kahlan\Plugin\Pointcut::before(__METHOD__, $__KPOINTCUT_SELF__, $__KPOINTCUT_ARGS__)) { $r = $__KPOINTCUT__($__KPOINTCUT_ARGS__, $__KPOINTCUT_SELF__); yield $r; }
        yield rand(2, 5);
    }

    public function voidMethod(): void
    {$__KPOINTCUT_ARGS__ = func_get_args(); $__KPOINTCUT_SELF__ = isset($this) ? $this : get_called_class(); if ($__KPOINTCUT__ = \Kahlan\Plugin\Pointcut::before(__METHOD__, $__KPOINTCUT_SELF__, $__KPOINTCUT_ARGS__)) { $r = $__KPOINTCUT__($__KPOINTCUT_ARGS__, $__KPOINTCUT_SELF__); }
    }
}
