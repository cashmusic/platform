<?php
namespace Kahlan\Jit\Node;

class FunctionDef extends NodeDef
{
    /**
     * The node's type.
     *
     * @var string
     */
    public $type = 'function';

    /**
     * Boolean indicating if this function is a closure.
     *
     * @var boolean
     */
    public $isClosure = false;

    /**
     * Boolean indicating if this function is a method.
     *
     * @var boolean
     */
    public $isMethod = false;

    /**
     * Boolean indicating if it's a void method.
     *
     * @var boolean
     */
    public $isVoid = false;

    /**
     * Boolean indicating if this function is a generator function.
     *
     * @var boolean
     */
    public $isGenerator = false;

    /**
     * Boolean indicating the visibilty of the method.
     *
     * @var boolean
     */
    public $visibility = [];

    /**
     * The name of the function.
     *
     * @var string
     */
    public $name = '';

    /**
     * The arguments of the function.
     *
     * @var array
     */
    public $args = [];

    /**
     * Returns function's arguments into a list of callable parameters
     *
     * @return string
     */
    public function argsToParams()
    {
        $args = [];
        foreach ($this->args as $key => $value) {
            $value = is_int($key) ? $value : $key;
            preg_match("/(\\\$[\\\a-z_\\x7f-\\xff][a-z0-9_\\x7f-\\xff]*)/i", $value, $match);
            $args[] = $match[1];
        }
        return join(', ', $args);
    }
}
