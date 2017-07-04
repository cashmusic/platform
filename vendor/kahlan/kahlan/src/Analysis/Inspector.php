<?php
namespace Kahlan\Analysis;

use ReflectionClass;

class Inspector
{
    /**
     * The ReflectionClass instances cache.
     *
     * @var array
     */
    protected static $_cache = [];

    /**
     * Gets the ReflectionClass instance of a class.
     *
     * @param  string $class The class name to inspect.
     * @return object        The ReflectionClass instance.
     */
    public static function inspect($class)
    {
        if (!isset(static::$_cache[$class])) {
            static::$_cache[$class] = new ReflectionClass($class);
        }
        return static::$_cache[$class];
    }

    /**
     * Gets the parameters array of a class method.
     *
     * @param  $class  The class name.
     * @param  $method The method name.
     * @param  $data   The default values.
     * @return array   The parameters array.
     */
    public static function parameters($class, $method, $data = null)
    {
        $params = [];
        $reflexion = Inspector::inspect($class);
        $parameters = $reflexion->getMethod($method)->getParameters();
        if ($data === null) {
            return $parameters;
        }
        foreach ($data as $key => $value) {
            $name = $key;
            if ($parameters) {
                $parameter = array_shift($parameters);
                $name = $parameter->getName();
            }
            $params[$name] = $value;
        }
        foreach ($parameters as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $params[$parameter->getName()] = $parameter->getDefaultValue();
            }
        }
        return $params;
    }

    /**
     * Returns the type hint of a `ReflectionParameter` instance.
     *
     * @param  object $parameter A instance of `ReflectionParameter`.
     * @return string            The parameter type hint.
     */
    public static function typehint($parameter)
    {
        $typehint = '';
        if ($parameter->getClass()) {
            $typehint = '\\' . $parameter->getClass()->getName();
        } elseif (preg_match('/.*?\[ \<[^\>]+\> (?:HH\\\)?(\w+)(.*?)\$/', (string) $parameter, $match)) {
            $typehint = $match[1];
            if ($typehint === 'integer') {
                $typehint = 'int';
            } elseif ($typehint === 'boolean') {
                $typehint = 'bool';
            } elseif ($typehint === 'mixed') {
                $typehint = '';
            }
        }
        return $typehint;
    }
}
