<?php namespace Pixie\QueryBuilder;

class Raw
{

    /**
     * @var string
     */
    protected $value;

    /**
     * @var array
     */
    protected $bindings;

    public function __construct($value, $bindings = array())
    {
        $this->value = (string)$value;
        $this->bindings = (array)$bindings;
    }

    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }
}
