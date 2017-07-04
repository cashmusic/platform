<?php
namespace Kahlan;

use Exception;

/**
 * Class Matcher
 *
 * @method Matcher toBe(mixed $expected) passes if actual === expected
 * @method Matcher toEqual(mixed $expected) passes if actual == expected
 * @method Matcher toBeTruthy() passes if actual is truthy
 * @method Matcher toBeFalsy() passes if actual is falsy
 * @method Matcher toBeEmpty() passes if actual is falsy
 * @method Matcher toBeNull() passes if actual is null
 * @method Matcher toBeA(string $expected) passes if actual is of the expected type
 * @method Matcher toBeAn(string $expected) passes if actual is of the expected type (toBeA alias)
 * @method Matcher toBeAnInstanceOf(string $expected) passes if actual is an instance of expected
 * @method Matcher toHaveLength(int $expected) passes if actual has the expected length
 * @method Matcher toContain(mixed $expected) passes if actual contain the expected value
 * @method Matcher toContainKey(mixed $expected) passes if actual contain the expected key
 * @method Matcher toContainKeys(mixed $expected) passes if actual contain the expected keys (toContainKey alias)
 * @method Matcher toBeCloseTo(float $expected, int $precision) passes if actual is close to expected in some precision
 * @method Matcher toBeGreaterThan(mixed $expected) passes if actual if greater than expected
 * @method Matcher toBeLessThan(mixed $expected) passes if actual is less than expected
 * @method Matcher toThrow(mixed $expected = null) passes if actual throws the expected exception
 * @method Matcher toMatch(string $expected) passes if actual matches the expected regexp
 * @method Matcher toEcho(string $expected) passes if actual echoes the expected string
 * @method Matcher toMatchEcho(string $expected) passes if actual echoes matches the expected string
 * @method Matcher toReceive(string $expected) passes if the expected method as been called on actual
 * @method Matcher toReceiveNext(string $expected) passes if the expected method as been called on actual after some other method
 */
class Matcher
{
    /**
     * Class dependencies.
     *
     * @var array
     */
    protected $_classes = [
        'specification' => 'Kahlan\Specification'
    ];

    /**
     * The matchers list
     *
     * @var array
     */
    protected static $_matchers = [];

    /**
     * Registers a matcher.
     *
     * @param string $name   The name of the matcher.
     * @param string $target An optionnal target class name.
     * @param string $class  A fully-namespaced class name.
     */
    public static function register($name, $class, $target = '')
    {
        static::$_matchers[$name][$target] = $class;
    }

    /**
     * Returns registered matchers.
     *
     * @param  string $name   The name of the matcher.
     * @param  string $target An optionnal target class name.
     *
     * @return array          The registered matchers or a fully-namespaced class name if $name is not null.
     * @throws Exception
     */
    public static function get($name = null, $target = '')
    {
        if (!func_num_args()) {
            return static::$_matchers;
        }
        if ($target === true) {
            return isset(static::$_matchers[$name]) ? static::$_matchers[$name] : [];
        }
        if ($target === '') {
            if (isset(static::$_matchers[$name][''])) {
                return static::$_matchers[$name][''];
            }
            throw new Exception("Unexisting default matcher attached to `'{$name}'`.");
        }
        if (isset(static::$_matchers[$name][$target])) {
            return static::$_matchers[$name][$target];
        } elseif (isset(static::$_matchers[$name][''])) {
            return static::$_matchers[$name][''];
        }
        throw new Exception("Unexisting matcher attached to `'{$name}'` for `{$target}`.");
    }

    /**
     * Checks if a matcher is registered.
     *
     * @param  string $name   The name of the matcher.
     * @param  string $target An optional target class name.
     *
     * @return boolean         Returns `true` if the matcher exists, `false` otherwise.
     */
    public static function exists($name, $target = '')
    {
        if ($target === true) {
            return isset(static::$_matchers[$name]);
        }
        return isset(static::$_matchers[$name][$target]);
    }

    /**
     * Unregisters a matcher.
     *
     * @param mixed  $name   The name of the matcher. If name is `true` unregister all the matchers.
     * @param string $target An optionnal target class name.
     */
    public static function unregister($name, $target = '')
    {
        if ($name === true) {
            static::$_matchers = [];
        } else {
            unset(static::$_matchers[$name][$target]);
        }
    }

    /**
     * Resets the class.
     */
    public static function reset()
    {
        static::$_matchers = [];
    }
}
