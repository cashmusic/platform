<?php
namespace Kahlan\Filter;

use Closure;

class MethodFilters
{
    /**
     * An array of filters indexed by method name
     *
     * @var array
     */
    protected $_filters = [];

    /**
     * Adds a new filter to a method.
     *
     * @param  string|array $methods Can either be a single method name as a string, or an array
     *                               of method names.
     * @param  string       $name    The filter name to apply.
     * @param  Closure      $closure The filter.
     * @return                       The name reference of the created filter.
     */
    public function apply($methods, $name, $closure)
    {
        foreach ((array) $methods as $method) {
            $this->_filters[$method][$name] = $closure;
        }
        return $name;
    }

    /**
     * Detaches a filter completely, by class/instance or on a method basis.
     *
     * @param string|null $method The name of a method. If `null` detaches all filters for all methods.
     * @param array|null  $name   The filter name to detach. If `null` detaches all filters.
     */
    public function detach($method = null, $name = null)
    {
        if ($method === null && $name === null) {
            $this->clear();
        }
        if ($name === null) {
            unset($this->_filters[$method]);
            return;
        }
        if ($method !== null) {
            unset($this->_filters[$method][$name]);
            return;
        }
        foreach ($this->_filters as $method => $value) {
            unset($this->_filters[$method][$name]);
        }
    }

    /**
     * Gets filters related to a method.
     *
     * @param  string  $method The method name.
     * @return array           The array of filters applied to the method.
     */
    public function filters($method)
    {
        if (!isset($this->_filters[$method])) {
            $this->_filters[$method] = [];
        }
        return $this->_filters[$method];
    }

    /**
     * Removes all filters.
     */
    public function clear()
    {
        $this->_filters = [];
    }
}
