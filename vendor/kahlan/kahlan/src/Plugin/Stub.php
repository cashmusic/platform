<?php
namespace Kahlan\Plugin;

use InvalidArgumentException;
use Reflection;
use ReflectionMethod;
use ReflectionClass;
use Kahlan\Suite;
use Kahlan\MissingImplementationException;
use Kahlan\Analysis\Inspector;
use Kahlan\Plugin\Stub\Method;

class Stub
{
    /**
     * Registered stubbed instance/class methods.
     *
     * @var array
     */
    protected static $_registered = [];

    /**
     * Method chain.
     *
     * @var Method[]
     */
    protected $_chain = [];

    /**
     * Stubbed methods.
     *
     * @var Method[]
     */
    protected $_methods = [];

    /**
     * Generic stubs.
     *
     * @var Method[]
     */
    protected $_stubs = [];

    /**
     * Stub index counter.
     *
     * @var integer
     */
    protected $_needToBePatched = false;

    /**
     * The Constructor.
     *
     * @param mixed $reference An instance or a fully-namespaced class name.
     */
    public function __construct($reference)
    {
        $reference = $this->_reference($reference);
        $isString = is_string($reference);
        if ($isString) {
            if (!class_exists($reference)) {
                throw new InvalidArgumentException("Can't Stub the unexisting class `{$reference}`.");
            }
            $reference = ltrim($reference, '\\');
            $reflection = Inspector::inspect($reference);
        } else {
            $reflection = Inspector::inspect(get_class($reference));
        }

        if (!$reflection->isInternal()) {
            $this->_reference = $reference;
            return;
        }
        if (!$isString) {
            throw new InvalidArgumentException("Can't Stub built-in PHP instances, create a test double using `Double::instance()`.");
        }
        $this->_needToBePatched = true;
        return $this->_reference = $reference;
    }

    /**
     * Return the actual reference which must be used.
     *
     * @param mixed $reference An instance or a fully-namespaced class name.
     * @param mixed            The reference or the monkey patched one if exist.
     */
    protected function _reference($reference)
    {
        if (!is_string($reference)) {
            return $reference;
        }

        $pos = strrpos($reference, '\\');
        if ($pos !== false) {
            $namespace = substr($reference, 0, $pos);
            $basename = substr($reference, $pos + 1);
        } else {
            $namespace = null;
            $basename = $reference;
        }
        $substitute = null;
        $reference = Monkey::patched($namespace, $basename, false, $substitute);

        return $substitute ?: $reference;
    }

    /**
     * Getd/Setd stubs for methods or get stubbed methods array.
     *
     * @param  array    $name An array of method names.
     * @return Method[]       Return the array of stubbed methods.
     */
    public function methods($name = [])
    {
        if (!func_num_args()) {
            return $this->_methods;
        }
        foreach ($name as $method => $returns) {
            if (is_callable($returns)) {
                $this->method($method, $returns);
            } elseif (is_array($returns)) {
                $stub = $this->method($method);
                call_user_func_array([$stub, 'andReturn'], $returns);
            } else {
                $error = "Stubbed method definition for `{$method}` must be a closure or an array of returned value(s).";
                throw new InvalidArgumentException($error);
            }
        }
    }

    /**
     * Stubs a method.
     *
     * @param  string   $path    Method name or array of stubs where key are method names and
     *                           values the stubs.
     * @param  string   $closure The stub implementation.
     * @return Method[]          The created array of method instances.
     * @return Method            The stubbed method instance.
     */
    public function method($path, $closure = null)
    {
        if ($this->_needToBePatched) {
            $layer = Double::classname();
            Monkey::patch($this->_reference, $layer);
            $this->_needToBePatched = false;
            $this->_reference = $layer;
        }

        $reference = $this->_reference;

        if (!$path) {
            throw new InvalidArgumentException("Method name can't be empty.");
        }

        $names = is_array($path) ? $path : [$path];

        $this->_chain = [];
        $total = count($names);

        foreach ($names as $index => $name) {
            if (preg_match('/^::.*/', $name)) {
                $reference = is_object($reference) ? get_class($reference) : $reference;
            }

            $hash = Suite::hash($reference);
            if (!isset(static::$_registered[$hash])) {
                static::$_registered[$hash] = new static($reference);
            }

            $instance = static::$_registered[$hash];
            if (is_object($reference)) {
                Suite::register(get_class($reference));
            } else {
                Suite::register($reference);
            }
            if (!isset($instance->_methods[$name])) {
                $instance->_methods[$name] = [];
                $instance->_stubs[$name] = Double::instance();
            }

            $method = new Method([
                'parent'    => $this,
                'reference' => $reference,
                'name'      => $name
            ]);
            $this->_chain[$name] = $method;
            array_unshift($instance->_methods[$name], $method);

            if ($index < $total - 1) {
                $reference = $instance->_stubs[$name];
                $method->andReturn($instance->_stubs[$name]);
            }
        }

        $method = end($this->_chain);
        if ($closure) {
            $method->andRun($closure);
        }
        return $method;
    }

    /**
     * Set arguments requirement indexed by method name.
     *
     * @param  mixed ... <0,n> Argument(s).
     * @return self
     */
    public function where($requirements = [])
    {
        foreach ($requirements as $name => $args) {
            if (!isset($this->_chain[$name])) {
                throw new InvalidArgumentException("Unexisting `{$name}` as method as part of the chain definition.");
            }
            if (!is_array($args)) {
                throw new InvalidArgumentException("Argument requirements must be an arrays for `{$name}` method.");
            }
            call_user_func_array([$this->_chain[$name], 'with'], $args);
        }
        return $this;
    }

    /**
     * Stubs class methods.
     *
     * @param  object|string $reference An instance or a fully-namespaced class name.
     * @return self                     The Stub instance.
     */
    public static function on($reference)
    {
        $hash = Suite::hash($reference);
        if (isset(static::$_registered[$hash])) {
            return static::$_registered[$hash];
        }
        return static::$_registered[$hash] = new static($reference);
    }

    /**
     * Finds a stub.
     *
     * @param  mixed       $references An instance or a fully namespaced class name.
     *                                 or an array of that.
     * @param  string      $method     The method name.
     * @param  array       $args       The required arguments.
     * @return object|null             Return the subbed method or `null` if not founded.
     */
    public static function find($references, $method = null, $args = null)
    {
        $references = (array) $references;
        $stub = null;
        $refs = [];
        foreach ($references as $reference) {
            $hash = Suite::hash($reference);
            if (!isset(static::$_registered[$hash])) {
                continue;
            }
            $stubs = static::$_registered[$hash]->methods();

            if (!isset($stubs[$method])) {
                continue;
            }

            foreach ($stubs[$method] as $stub) {
                $call['name'] = $method;
                $call['args'] = $args;
                if ($stub->match($call)) {
                    return $stub;
                }
            }
        }
        return false;
    }

    /**
     * Checks if a stub has been registered for a hash
     *
     * @param  mixed         $hash An instance hash or a fully namespaced class name.
     * @return boolean|array
     */
    public static function registered($hash = null)
    {
        if (!func_num_args()) {
            return array_keys(static::$_registered);
        }
        return isset(static::$_registered[$hash]);
    }

    /**
     * Clears the registered references.
     *
     * @param string $reference An instance or a fully namespaced class name or `null` to clear all.
     */
    public static function reset($reference = null)
    {
        if ($reference === null) {
            static::$_registered = [];
            Suite::reset();
            return;
        }
        unset(static::$_registered[Suite::hash($reference)]);
    }
}
