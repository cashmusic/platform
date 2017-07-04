<?php
namespace Kahlan;

use Exception;
use Kahlan\Analysis\Debugger;
use Kahlan\Analysis\Inspector;
use Kahlan\Code\Code;
use Kahlan\Code\TimeoutException;
use Closure;

/**
 * Class Expectation
 *
 * @method Expectation toBe(mixed $expected) passes if actual === expected
 * @method Expectation toEqual(mixed $expected) passes if actual == expected
 * @method Expectation toBeTruthy() passes if actual is truthy
 * @method Expectation toBeFalsy() passes if actual is falsy
 * @method Expectation toBeEmpty() passes if actual is falsy
 * @method Expectation toBeNull() passes if actual is null
 * @method Expectation toBeA(string $expected) passes if actual is of the expected type
 * @method Expectation toBeAn(string $expected) passes if actual is of the expected type (toBeA alias)
 * @method Expectation toBeAnInstanceOf(string $expected) passes if actual is an instance of expected
 * @method Expectation toHaveLength(int $expected) passes if actual has the expected length
 * @method Expectation toContain(mixed $expected) passes if actual contain the expected value
 * @method Expectation toContainKey(mixed $expected) passes if actual contain the expected key
 * @method Expectation toContainKeys(mixed $expected) passes if actual contain the expected keys (toContainKey alias)
 * @method Expectation toBeCloseTo(float $expected, int $precision) passes if actual is close to expected in some precision
 * @method Expectation toBeGreaterThan(mixed $expected) passes if actual if greater than expected
 * @method Expectation toBeLessThan(mixed $expected) passes if actual is less than expected
 * @method Expectation toThrow(mixed $expected = null) passes if actual throws the expected exception
 * @method Expectation toMatch(string $expected) passes if actual matches the expected regexp
 * @method Expectation toEcho(string $expected) passes if actual echoes the expected string
 * @method Expectation toMatchEcho(string $expected) passes if actual echoes matches the expected string
 * @method Expectation toReceive(string $expected) passes if the expected method as been called on actual
 * @method Expectation toReceiveNext(string $expected) passes if the expected method as been called on actual after some other method
 *
 * @property Expectation $not
 */
class Expectation
{
    /**
     * Deferred expectation.
     *
     * @var array
     */
    protected $_deferred = null;

    /**
     * Stores the success value.
     *
     * @var boolean
     */
    protected $_passed = null;

    /**
     * The result logs.
     *
     * @var array
     */
    protected $_logs = [];

    /**
     * The current value to test.
     *
     * @var mixed
     */
    protected $_actual = null;

    /**
     * If `true`, the result of the test will be inverted.
     *
     * @var boolean
     */
    protected $_not = false;

    /**
     * The timeout value.
     *
     * @var integer
     */
    protected $_timeout = -1;

    /**
     * Constructor.
     *
     * @param array $config The config array. Options are:
     *                       -`'actual'`  _mixed_   : the actual value.
     *                       -`'timeout'` _integer_ : the timeout value.
     */
    public function __construct($config = [])
    {
        $defaults = [
            'actual'  => null,
            'timeout' => -1
        ];
        $config += $defaults;

        $this->_actual = $config['actual'];
        $this->_timeout = $config['timeout'];
    }

    /**
     * Returns the actual value.
     *
     * @return boolean
     */
    public function actual()
    {
        return $this->_actual;
    }

    /**
     * Returns the not value.
     *
     * @return boolean
     */
    public function not()
    {
        return $this->_not;
    }

    /**
     * Returns the logs.
     */
    public function logs()
    {
        return $this->_logs;
    }

    /**
     * Returns the deferred expectations.
     *
     * @return array
     */
    public function deferred()
    {
        return $this->_deferred;
    }

    /**
     * Returns the timeout value.
     */
    public function timeout()
    {
        return $this->_timeout;
    }

    /**
     * Calls a registered matcher.
     *
     * @param  string  $matcherName The name of the matcher.
     * @param  array   $args        The arguments to pass to the matcher.
     * @return boolean
     */
    public function __call($matcherName, $args)
    {
        $result = true;
        $spec = $this->_actual;
        $this->_passed = true;

        $closure = function () use ($spec, $matcherName, $args, &$actual, &$result) {
            if ($spec instanceof Specification) {
                $actual = null;
                if (!$spec->passed($actual)) {
                    return false;
                }
            } else {
                $actual = $spec;
            }
            array_unshift($args, $actual);
            $matcher = $this->_matcher($matcherName, $actual);
            $result = call_user_func_array($matcher . '::match', $args);
            return is_object($result) || $result === !$this->_not;
        };

        try {
            $this->_spin($closure);
        } catch (TimeoutException $e) {
            $data['data']['timeout'] = $e->getMessage();
        }

        array_unshift($args, $actual);
        $matcher = $this->_matcher($matcherName, $actual);
        $data = Inspector::parameters($matcher, 'match', $args);
        $report = compact('matcherName', 'matcher', 'data');
        if ($spec instanceof Specification) {
            foreach ($spec->logs() as $value) {
                $this->_logs[] = $value;
            }
            $this->_passed = $spec->passed() && $this->_passed;
        }

        if (!is_object($result)) {
            $report['description'] = $report['matcher']::description();
            $this->_log($result, $report);

            return $this;
        }
        $this->_deferred = $report + [
            'instance' => $result, 'not' => $this->_not,
        ];

        return $result;
    }

    /**
     * Returns a compatible matcher class name according to a passed actual value.
     *
     * @param  string $matcherName The name of the matcher.
     * @param  mixed  $actual      The actual value.
     * @return string              A matcher class name.
     */
    public function _matcher($matcherName, $actual)
    {
        if (!Matcher::exists($matcherName, true)) {
            throw new Exception("Unexisting matcher attached to `'{$matcherName}'`.");
        }

        $matcher = null;

        foreach (Matcher::get($matcherName, true) as $target => $value) {
            if (!$target) {
                $matcher = $value;
                continue;
            }
            if ($actual instanceof $target) {
                $matcher = $value;
            }
        }

        if (!$matcher) {
            throw new Exception("Unexisting matcher attached to `'{$matcherName}'` for `{$target}`.");
        }

        return $matcher;
    }

    /**
     * Processes the expectation.
     *
     * @param  string $matcher The matcher class name.
     * @param  array  $args    The parameters to pass to the matcher.
     *
     * @return mixed
     */
    protected function _run()
    {
        if ($this->_passed !== null) {
            return $this;
        }
        $spec = $this->_actual;
        if (!$spec instanceof Specification) {
            return $this;
        }

        $closure = function () use ($spec) {
            $success = true;
            try {
                $success = $spec->passed();
            } catch (Exception $e) {
            }
            return $success;
        };

        try {
            $this->_spin($closure);
        } catch (TimeoutException $e) {
        }
        $this->_logs = $spec->logs();
        $this->_passed = $spec->passed() && $this->_passed;

        return $this;
    }

    /**
     * Runs the expectation.
     *
     * @param Closure $closure The closure to run/spin.
     */
    protected function _spin($closure)
    {
        if (($timeout = $this->timeout()) < 0) {
            $closure();
        } else {
            Code::spin($closure, $timeout);
        }
    }

    /**
     * Resolves deferred matchers.
     */
    protected function _resolve()
    {
        if (!$this->_deferred) {
            return;
        }
        $data = $this->_deferred;

        $instance = $data['instance'];
        $this->_not = $data['not'];
        $boolean = $instance->resolve();
        $data['description'] = $instance->description();
        $data['backtrace'] = $instance->backtrace();
        $this->_log($boolean, $data);

        $this->_deferred = null;
    }

    /**
     * Logs a result.
     *
     * @param  boolean $boolean Set `true` for success and `false` for failure.
     * @param  array   $data    Test details array.
     * @return boolean
     */
    protected function _log($boolean, $data = [])
    {
        $not = $this->_not;
        $pass = $not ? !$boolean : $boolean;
        if ($pass) {
            $data['type'] = 'passed';
        } else {
            $data['type'] = 'failed';
            $this->_passed = false;
        }

        $description = $data['description'];
        if (is_array($description)) {
            $data['data'] = $description['data'];
            $data['description'] = $description['description'];
        }
        $data += ['backtrace' => Debugger::backtrace()];
        $data['not'] = $not;

        $this->_logs[] = $data;
        $this->_not = false;
        return $boolean;
    }

    /**
     * Magic getter, if called with `'not'` invert the `_not` attribute.
     *
     * @param string
     */
    public function __get($name)
    {
        if ($name !== 'not') {
            throw new Exception("Unsupported attribute `{$name}`.");
        }
        $this->_not = !$this->_not;
        return $this;
    }

    /**
     * Checks if all test passed.
     *
     * @return boolean Returns `true` if no error occurred, `false` otherwise.
     */
    public function passed()
    {
        $this->_run();
        $this->_resolve();
        return $this->_passed !== false;
    }

    /**
     * Clears the instance.
     */
    public function clear()
    {
        $this->_actual = null;
        $this->_passed = null;
        $this->_not = false;
        $this->_timeout = -1;
        $this->_logs = [];
        $this->_deferred = null;
    }
}
