<?php
namespace Kahlan\Plugin;

use Kahlan\Suite;
use Kahlan\Plugin\Call\Calls;

class Pointcut
{
    /**
     * Class dependencies.
     *
     * @var array
     */
    protected static $_classes = [
        'stub'  => 'Kahlan\Plugin\Stub'
    ];

    /**
     * Point cut called before method execution.
     *
     * @return boolean If `true` is returned, the normal execution of the method is aborted.
     */
    public static function before($method, $self, &$args)
    {
        if (!Suite::registered()) {
            return false;
        }

        list($class, $name) = explode('::', $method);

        $lsb = is_object($self) ? get_class($self) : $self;

        if (!Suite::registered($lsb) && !Suite::registered($class)) {
            return false;
        }

        if ($name === '__call' || $name === '__callStatic') {
            $name = array_shift($args);
            $args = array_shift($args);
        }

        return static::_stubbedMethod($lsb, $self, $class, $name, $args);
    }

    /**
     * Checks if the called method has been stubbed.
     *
     * @param  string $lsb         Late state binding class name.
     * @param  object|string $self The object instance or a fully-namespaces class name.
     * @param  string $class       The class name.
     * @param  string $name        The method name.
     * @param  string $args        The passed arguments.
     * @return boolean             Returns `true` if the method has been stubbed.
     */
    protected static function _stubbedMethod($lsb, $self, $class, $name, $args)
    {
        if (is_object($self)) {
            $list = $lsb === $class ? [$self, $lsb] : [$self, $lsb, $class];
        } else {
            $list = $lsb === $class ? [$lsb] : [$lsb, $class];
            $name = '::' . $name;
        }

        $stub = static::$_classes['stub'];

        $method = $stub::find($list, $name, $args);
        Calls::log($list, compact('name', 'args', 'method'));

        return $method ?: false;
    }
}
