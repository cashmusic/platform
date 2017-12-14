<?php namespace Viocon;

/**
 * This class gives the ability to access non-static methods statically
 *
 * Class AliasFacade
 *
 * @package Viocon
 */
class AliasFacade {

    /**
     * @var Container
     */
    protected static $vioconInstance;

    /**
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if(!static::$vioconInstance) {
            static::$vioconInstance = new Container();
        }

        return call_user_func_array(array(static::$vioconInstance, $method), $args);
    }

    /**
     * @param Container $instance
     */
    public static function setVioconInstance(Container $instance)
    {
        static::$vioconInstance = $instance;
    }

    /**
     * @return \Viocon\Container $instance
     */
    public static function getVioconInstance()
    {
        return static::$vioconInstance;
    }
}