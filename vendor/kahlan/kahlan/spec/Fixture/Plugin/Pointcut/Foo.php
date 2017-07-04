<?php
namespace Kahlan\Spec\Fixture\Plugin\Pointcut;

use Kahlan\Spec\Fixture\Plugin\Pointcut\Bar;

class Foo
{
    protected $_inited = false;

    protected $_status = 'none';

    protected $_message = 'Hello World!';

    protected static $_messageStatic = 'Hello Static World!';

    public function __construct()
    {
        $this->_inited = true;
    }

    public function inited()
    {
        return $this->_inited;
    }

    public function message($message = null)
    {
        if ($message === null) {
            return $this->_message;
        }
        $this->_message = $message;
    }

    public static function messageStatic($message = null)
    {
        if ($message === null) {
            return static::$_messageStatic;
        }
        static::$_messageStatic = $message;
    }

    public function bar()
    {
        $bar = new Bar();
        return $bar->send();
    }

    public function __call($name, $args)
    {
    }

    public static function __callStatic($name, $args)
    {
    }

    public static function version()
    {
        return '0.0.8b';
    }
}
