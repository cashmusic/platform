<?php
namespace Kahlan;

use Exception;
use Kahlan\Plugin\Stub;
use Kahlan\Plugin\Double;
use Kahlan\Plugin\Monkey;

class Allow
{
    /**
     * A fully-namespaced function name.
     *
     * @var string|object
     */
    protected $_actual = null;

    /**
     * The stub.
     *
     * @var string|object
     */
    protected $_stub = null;

    /**
     * The method instance.
     *
     * @var object
     */
    protected $_method = null;

    /**
     * Boolean indicating if actual is a class or not.
     *
     * @var boolean
     */
    protected $_isClass = false;

    /**
     * Constructor
     *
     * @param string|object $actual   A fully-namespaced class name or an object instance.
     * @param string        $expected The expected method method name to be called.
     */
    public function __construct($actual)
    {
        if (is_string($actual)) {
            $actual = ltrim($actual, '\\');
        }

        if (!is_string($actual) || class_exists($actual)) {
            $this->_isClass = true;
            $this->_stub = Stub::on($actual);
        }
        $this->_actual = $actual;
    }

    /**
     * Stub a chain of methods.
     *
     * @param  string $expected the method to be stubbed or a chain of methods.
     * @return        self.
     */
    public function toReceive()
    {
        if (!$this->_isClass) {
            throw new Exception("Error `toReceive()` are only available on classes/instances not functions.");
        }
        return $this->_method = $this->_stub->method(func_get_args());
    }

    /**
     * Stub function.
     *
     * @return        self.
     */
    public function toBeCalled()
    {
        if ($this->_isClass) {
            throw new Exception("Error `toBeCalled()` are are only available on functions not classes/instances.");
        }
        return Monkey::patch($this->_actual);
    }

    /**
     * Sets the stub logic.
     *
     * @param mixed ... The substitue(s).
     */
    public function toBe()
    {
        if (!is_string($this->_actual)) {
            throw new Exception("Error `toBe()` need to be applied on a fully-namespaced class or function name.");
        }
        $method = Monkey::patch($this->_actual);
        call_user_func_array([$method, 'toBe'], func_get_args());
    }

    /**
     * Sets the stub logic.
     *
     * @param mixed $substitute The logic.
     */
    public function toBeOK()
    {
        if (!is_string($this->_actual)) {
            throw new Exception("Error `toBeOK()` need to be applied on a fully-namespaced class or function name.");
        }
        if ($this->_isClass) {
            Monkey::patch($this->_actual, Double::classname());
        } else {
            Monkey::patch($this->_actual, function () {
            });
        }
    }

    /**
     * Set return values.
     *
     * @param mixed ... <0,n> Return value(s).
     */
    public function andReturn()
    {
        throw new Exception("You must to call `toReceive()/toBeCalled()` before defining a return value.");
    }

    /**
     * Set return values.
     *
     * @param mixed ... <0,n> Return value(s).
     */
    public function andRun()
    {
        throw new Exception("You must to call `toReceive()/toBeCalled()` before defining a return value.");
    }
}
