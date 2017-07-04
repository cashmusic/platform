<?php
namespace Kahlan;

use Exception;

/**
 * Class Arg
 *
 * @method static Arg toBe(mixed $expected) passes if actual === expected
 * @method static Arg toEqual(mixed $expected) passes if actual == expected
 * @method static Arg toBeTruthy() passes if actual is truthy
 * @method static Arg toBeFalsy() passes if actual is falsy
 * @method static Arg toBeEmpty() passes if actual is falsy
 * @method static Arg toBeNull() passes if actual is null
 * @method static Arg toBeA(string $expected) passes if actual is of the expected type
 * @method static Arg toBeAn(string $expected) passes if actual is of the expected type (toBeA alias)
 * @method static Arg toBeAnInstanceOf(string $expected) passes if actual is an instance of expected
 * @method static Arg toHaveLength(int $expected) passes if actual has the expected length
 * @method static Arg toContain(mixed $expected) passes if actual contain the expected value
 * @method static Arg toContainKey(mixed $expected) passes if actual contain the expected key
 * @method static Arg toContainKeys(mixed $expected) passes if actual contain the expected keys (toContainKey alias)
 * @method static Arg toBeCloseTo(float $expected, int $precision) passes if actual is close to expected in some precision
 * @method static Arg toBeGreaterThan(mixed $expected) passes if actual if greater than expected
 * @method static Arg toBeLessThan(mixed $expected) passes if actual is less than expected
 * @method static Arg toThrow(mixed $expected = null) passes if actual throws the expected exception
 * @method static Arg toMatch(string $expected) passes if actual matches the expected regexp
 * @method static Arg toEcho(string $expected) passes if actual echoes the expected string
 * @method static Arg toMatchEcho(string $expected) passes if actual echoes matches the expected string
 * @method static Arg toReceive(string $expected) passes if the expected method as been called on actual
 * @method static Arg toReceiveNext(string $expected) passes if the expected method as been called on actual after some other method
 */
class Arg
{
    /**
     * Class dependencies.
     *
     * @var array
     */
    protected static $_classes = [
        'matcher' => 'Kahlan\Matcher'
    ];

    /**
     * The matcher name.
     *
     * @var string
     */
    protected $_name = '';

    /**
     * The array of fully namespaced matcher classname.
     *
     * @var array
     */
    protected $_matchers = [];

    /**
     * The expected arguments.
     *
     * @var array
     */
    protected $_args = [];

    /**
     * If `true`, the result of the test will be inverted.
     *
     * @var boolean
     */
    protected $_not = false;

    /**
     * Constructor
     *
     * @param array $config The argument matcher options. Possible values are:
     *                      - `'not'`     _boolean_: indicate if the matcher is a negative matcher.
     *                      - `'matcher'` _string_ : the fully namespaced matcher class name.
     *                      - `'args'`    _string_ : the expected arcuments.
     */
    public function __construct($config = [])
    {
        $defaults = ['name' => '', 'not' => false, 'matchers' => [], 'args' => []];
        $config += $defaults;

        $this->_name     = $config['name'];
        $this->_not      = $config['not'];
        $this->_matchers = $config['matchers'];
        $this->_args     = $config['args'];
    }

    /**
     * Create an Argument Matcher
     *
     * @param  string  $name The name of the matcher.
     * @param  array   $args The arguments to pass to the matcher.
     * @return boolean
     */
    public static function __callStatic($name, $args)
    {
        $not = false;
        if (preg_match('/^not/', $name)) {
            $matcher = lcfirst(substr($name, 3));
            $not = true;
        } else {
            $matcher = $name;
        }
        $class = static::$_classes['matcher'];
        if ($matchers = $class::get($matcher, true)) {
            return new static(compact('name', 'matchers', 'not', 'args'));
        }
        throw new Exception("Unexisting matchers attached to `'{$name}'`.");
    }

    /**
     * Check if `$actual` matches the matcher.
     *
     * @param  string  $actual The actual value.
     * @return boolean         Returns `true` on success and `false` otherwise.
     */
    public function match($actual)
    {
        $matcher = null;
        foreach ($this->_matchers as $target => $value) {
            if (!$target) {
                $matcher = $value;
                continue;
            }
            if ($actual instanceof $target) {
                $matcher = $value;
            }
        }
        if (!$matcher) {
            throw new Exception("Unexisting matcher attached to `'{$this->_name}'` for `{$target}`.");
        }
        $args = $this->_args;
        array_unshift($args, $actual);
        $boolean = call_user_func_array($matcher . '::match', $args);
        return $this->_not ? !$boolean : $boolean;
    }
}
