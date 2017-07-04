<?php
namespace Kahlan\Filter;

use Closure;
use Exception;

class Filter
{
    /**
     * Indicates if the filter system is enabled or not.
     *
     * @var boolean
     */
    protected static $_enabled = true;

    /**
     * An array of `Closure` indexed by a name identifier.
     *
     * @var array
     */
    protected static $_aspects = [];

    /**
     * An array of `MethodFilters` indexed by class name.
     *
     * @var array
     */
    protected static $_methodFilters = [];

    /**
     * A cached array of filters indexed by class name & method name.
     *
     * @var array
     */
    protected static $_cachedFilters = [];

    /**
     * Registers an aspect in the system.
     *
     * @param  string|array|Closure $name     The aspect name identifier, the aspects array or the aspect closure.
     * @param  Closure              $callback The aspect closure when `$name` is a string.
     * @return string|null                    The registered aspect name identifier or `null` for multiple registration.
     */
    public static function register($name, $aspect = null)
    {
        if (is_array($name)) {
            static::$_aspects = $name;
            return;
        }
        if ($aspect === null) {
            $aspect = $name;
            $name = uniqid();
        }
        static::$_aspects[$name] = $aspect;
        return $name;
    }

    /**
     * Unregisters an aspect in the system.
     *
     * @param string $name The aspect name identifier to unregister.
     */
    public static function unregister($name)
    {
        unset(static::$_aspects[$name]);
    }

    /**
     * Checks if as aspect exists or returns all registered aspects if `$name` is `null`.
     *
     * @param  string|null $name The aspect name identifier or `null` to get all registered aspects.
     * @return boolean
     */
    public static function registered($name = null)
    {
        if ($name === null) {
            return static::$_aspects;
        }
        return isset(static::$_aspects[$name]);
    }

    /**
     * Applies a filter to a method.
     *
     * @param  mixed        $context  The instance or class name context to apply a new filter.
     * @param  string|array $methods  The name or array of method names to be filtered.
     * @param  string       $name     The filter name to apply.
     * @return string                 The name reference of the applied filter.
     * @throws Exception
     */
    public static function apply($context, $methods, $name)
    {
        if (!isset(static::$_aspects[$name])) {
            throw new Exception("Undefined filter `{$name}`.");
        }
        $aspect = static::$_aspects[$name];
        if (is_object($context)) {
            $aspect = $aspect->bindTo($context, get_class($context));
        } elseif (is_string($context)) {
            $aspect = $aspect->bindTo(null, $context);
            unset(static::$_cachedFilters[$context]);
        } else {
            throw new Exception("Error this context can't be filtered.");
        }
        $methodFilters = static::_methodFilters($context);
        return $methodFilters->apply($methods, $name, $aspect);
    }

    /**
     * Detaches a filter completely, by class/instance or on a method basis.
     *
     * @param  mixed  $context   The instance or class name context to apply a new filter.
     * @param  string $method    The name of the method to apply the filter.
     * @param  string $name|null The filter name to detach. If `null` detaches all filters.
     */
    public static function detach($context, $method, $name = null)
    {
        $methodFilters = static::_methodFilters($context);
        $methodFilters->detach($method, $name);
    }

    /**
     * Gets the `MethodFilters` instance of a context.
     *
     * @param  string|object $context The class/instance context.
     * @return object
     */
    protected static function _methodFilters($context)
    {
        if (is_object($context)) {
            return $context->methodFilters();
        }
        if (!isset(static::$_methodFilters[$context])) {
            static::$_methodFilters[$context] = new MethodFilters();
        }
        return static::$_methodFilters[$context];
    }

    /**
     * Gets the whole filters data or filters associated to a class/instance's method.
     * Or sets the whole filters data.
     *
     * @param  mixed       $context If `null` returns the whole filters data.
     *                              If `$context` is an array use `$context` as the whole filters data.
     *                              Otherwise `$context` stands for the class/instance context.
     * @param  string|null $method  The name of the method to get the filters from or `null` to get all of them.
     * @return array                The whole filters data or filters associated to a class/instance's method.
     */
    public static function filters($context = null, $method = null)
    {
        if (!func_num_args()) {
            return static::$_methodFilters;
        }
        if (is_array($context)) {
            return static::$_methodFilters = $context;
        }

        $result = [];

        if (is_object($context)) {
            $result = $context->methodFilters()->filters($method);
            $context = get_class($context);
        }

        return array_merge($result, static::_classFilters($context, $method));
    }

    /**
     * Gets the whole filters associated to a class/instance's method.
     *
     * @param  mixed   $context The class/instance context.
     * @param  string  $method  The name of the method to get the filters from.
     * @return array            The whole filters data or filters associated to a class/instance's method.
     */
    public static function _classFilters($context, $method)
    {
        if (isset(static::$_cachedFilters[$context][$method])) {
            return static::$_cachedFilters[$context][$method];
        }

        $result = [];
        $current = $context;
        do {
            if (!isset(static::$_methodFilters[$current])) {
                continue;
            }
            $result = array_merge($result, static::$_methodFilters[$current]->filters($method));
        } while ($current = get_parent_class($current));

        return static::$_cachedFilters[$context][$method] = $result;
    }

    /**
     * Cuts the normal execution of a method to run all applied filter for the method.
     *
     * @param  mixed   $context  The instance or class name context to perform the filtering on.
     * @param  string  $method   The name of the method which is going the be filtered.
     * @param  array   $params   The parameters passed to the original method.
     * @param  Closure $callback The original method logic closure.
     * @param  array   $filters  Additional filters to apply to the method for this call only.
     * @return mixed             Returns The result of the chain.
     */
    public static function on($context, $method, $params, $callback, $filters = [])
    {
        if (!static::$_enabled) {
            array_unshift($params, null);
            return call_user_func_array($callback, $params);
        }
        $filters = array_merge(static::filters($context, $method), $filters, [$callback]);
        $chain = new Chain(compact('filters', 'method', 'params'));
        $closure = $chain->rewind();
        array_unshift($params, $chain);
        return call_user_func_array($closure, $params);
    }

    /**
     * Enables/disables the filter system.
     *
     * @param boolean $active
     */
    public static function enable($active = true)
    {
        static::$_enabled = $active;
    }

    /**
     * Removes filters for all classes.
     */
    public static function reset()
    {
        static::$_aspects = [];
        static::$_methodFilters = [];
    }
}
