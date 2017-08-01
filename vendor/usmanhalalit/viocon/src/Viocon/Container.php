<?php namespace Viocon;

class Container
{
    /**
     * @var array
     */
    public $registry = array();

    /**
     * Singleton instances
     *
     * @var array
     */
    public $singletons = array();

    public function __construct($alias = null)
    {
        if ($alias) {
            AliasFacade::setVioconInstance($this);
            class_alias('\\Viocon\\AliasFacade', $alias);
        }
    }

    /**
     * Register an object with a key
     *
     * @param  string $key
     * @param  mixed  $object
     * @param  bool   $singleton
     *
     * @return void
     */
    public function set($key, $object, $singleton = false)
    {
        $this->registry[$key] = compact('object', 'singleton');
    }

    /**
     * If we have a registry for the given key
     *
     * @param  string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->registry);
    }

    /**
     * Register as singleton.
     *
     * @param  string $key
     * @param  mixed  $object
     *
     * @return void
     */
    public function singleton($key, $object)
    {
        $this->set($key, $object, true);
    }

    /**
     * Register or replace an instance as a singleton.
     * Useful for replacing with Mocked instance
     *
     * @param  string $key
     * @param  mixed  $instance
     *
     * @return void
     */
    public function setInstance($key, $instance)
    {
        $this->singletons[$key] = $instance;
    }

    /**
     * Build from the given key.
     * If there is a class registered with Container::set() then it's instance
     * will be returned. If a closure is registered, a closure's return value
     * will be returned. If nothing is registered then it will try to build an
     * instance with new $key(...).
     *
     * $parameters will be passed to closure or class constructor.
     *
     *
     * @param  string $key
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function build($key, $parameters = array())
    {
        // If we have a singleton instance registered the just return it
        if (array_key_exists($key, $this->singletons)) {
            return $this->singletons[$key];
        }

        // If we don't have a registered object with the key then assume user
        // is trying to build a class with the given key/name

        if (!array_key_exists($key, $this->registry)) {
            $object = $key;
        } else {
            $object = $this->registry[$key]['object'];
        }


        $instance = $this->instanciate($object, $parameters);

        // If the key is registered as a singleton, we can save the instance as singleton
        // for later use
        if (isset($this->registry[$key]['singleton']) && $this->registry[$key]['singleton'] === true) {
            $this->singletons[$key] = $instance;
        }

        return $instance;
    }

    /**
     * Instantiate an instance of the given type.
     *
     * @param  string $key
     * @param  array  $parameters
     *
     * @throws \Exception
     * @return mixed
     */
    protected function instanciate($key, $parameters = null)
    {

        if ($key instanceof \Closure) {
            return call_user_func_array($key, $parameters);
        }

        $reflection = new \ReflectionClass($key);
        return $reflection->newInstanceArgs($parameters);
    }
}