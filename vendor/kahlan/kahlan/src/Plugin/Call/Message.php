<?php
namespace Kahlan\Plugin\Call;

class Message
{
    /**
     * Class dependencies.
     *
     * @var array
     */
    protected $_classes = [
        'arg' => 'Kahlan\Arg'
    ];

    /**
     * Parent instance.
     *
     * @var mixed
     */
    protected $_parent = null;

    /**
     * Message reference.
     *
     * @var mixed
     */
    protected $_reference = null;

    /**
     * Message name.
     *
     * @var string
     */
    protected $_name = null;

    /**
     * Message arguments.
     *
     * @var array
     */
    protected $_args = null;

    /**
     * Static call.
     *
     * @var boolean
     */
    protected $_static = false;

    /**
     * The Constructor.
     *
     * @param array $config Possible options are:
     *                       - `'reference'` _mixed_  : The message reference.
     *                       - `'name'`      _string_ : The message name.
     *                       - `'args'`      _array_  : The message arguments.
     *                       - `'static'`    _boolean_: `true` if the method is static, `false` otherwise.
     */
    public function __construct($config = [])
    {
        $defaults = [
            'parent' => null,
            'reference' => null,
            'name' => '',
            'args' => null,
            'static' => false
        ];
        $config += $defaults;

        $static = $config['static'];
        $name = $config['name'];
        if (preg_match('/^::.*/', $name)) {
            $static = true;
            $name = substr($name, 2);
        }

        $this->_parent = $config['parent'];
        $this->_reference = $config['reference'];
        $this->_name = $name;
        $this->_args = $config['args'];
        $this->_static = $static;
    }

    /**
     * Set arguments requirement.
     *
     * @param  mixed ... <0,n> Argument(s).
     * @return self
     */
    public function with()
    {
        $this->_args = func_get_args();
        return $this;
    }

    /**
     * Set arguments requirement indexed by method name.
     *
     * @param  mixed ... <0,n> Argument(s).
     * @return self
     */
    public function where($requirements = [])
    {
        $this->_parent->where($requirements);
        return $this;
    }

    /**
     * Check if this message is compatible with passed call array.
     *
     * @param  array   $call     A call array.
     * @param  boolean $withArgs Boolean indicating if matching should take arguments into account.
     * @return boolean
     */
    public function match($call, $withArgs = true)
    {
        if (preg_match('/^::.*/', $call['name'])) {
            $call['static'] = true;
            $call['name'] = substr($call['name'], 2);
        }

        if (isset($call['static'])) {
            if ($call['static'] !== $this->_static) {
                return false;
            }
        }

        if ($call['name'] !== $this->_name) {
            return false;
        }

        if ($withArgs) {
            return $this->matchArgs($call['args']);
        }

        return true;
    }

    /**
     * Check if this stub is compatible with passed args.
     *
     * @param  array   $args The passed arguments.
     * @return boolean
     */
    public function matchArgs($args)
    {
        if ($this->_args === null || $args === null) {
            return true;
        }
        $arg = $this->_classes['arg'];
        foreach ($this->_args as $expected) {
            $actual = array_shift($args);
            if ($expected instanceof $arg) {
                if (!$expected->match($actual)) {
                    return false;
                }
            } elseif ($actual !== $expected) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the parent.
     *
     * @return mixed
     */
    public function parent()
    {
        return $this->_parent;
    }

    /**
     * Get the message reference.
     *
     * @return mixed
     */
    public function reference()
    {
        return $this->_reference;
    }

    /**
     * Get message name.
     *
     * @return string
     */
    public function name()
    {
        return $this->isStatic() ? '::' . $this->_name : $this->_name;
    }

    /**
     * Get message arguments.
     *
     * @return array
     */
    public function args()
    {
        return $this->_args;
    }

    /**
     * Check if the method is a static method.
     *
     * @return boolean
     */
    public function isStatic()
    {
        return $this->_static;
    }
}
