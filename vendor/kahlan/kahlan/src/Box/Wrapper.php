<?php
namespace Kahlan\Box;

class Wrapper
{
    /**
     * The related box instance
     *
     * @var object
     */
    protected $__box = null;

    /**
     * The name of a dependency definition
     *
     * @var string
     */
    protected $__name = null;

    /**
     * The parameters to use for resolving the dependency.
     *
     * @var array
     */
    protected $__params = [];

    /**
     * Boolean indicating if the dependency has been resolved or not.
     *
     * @var boolean
     */
    protected $__resolved = false;

    /**
     * The resolved dependency.
     *
     * @var array
     */
    protected $__dependency = null;

    /**
     * The constructor
     *
     * @param  array        $config The config array. Possible values are:
     *                              - `'box'`    _object_: The box instance (required).
     *                              - `'name'`   _string_: The name of the dependency definition.
     *                              - `'params'` _array_ : The parameters to use for resolving the dependency.
     * @throws BoxException
     */
    public function __construct($config = [])
    {
        $defaults = [
            'box' => null,
            'name' => null,
            'params' => []
        ];
        $config += $defaults;

        $this->__box = $config['box'];
        $this->__name = $config['name'];
        $this->__params = $config['params'];

        if (!$this->__box || !$this->__name) {
            throw new BoxException("Error, the wrapper require at least `'box'` & `'name'` to not be empty.");
        }
    }

    /**
     * Resolve the dependency.
     *
     * @return mixed        The shared variable or an new instance.
     * @param  mixed  ...   A list of parameters.
     * @throws BoxException
     */
    public function get()
    {
        if ($this->__resolved) {
            return $this->__dependency;
        }
        $this->__resolved = true;
        $params = func_num_args() === 0 ? $this->__params : func_get_args();
        array_unshift($params, $this->__name);
        return $this->__dependency = call_user_func_array([$this->__box, 'get'], $params);
    }
}
