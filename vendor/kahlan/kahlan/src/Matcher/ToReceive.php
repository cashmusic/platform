<?php
namespace Kahlan\Matcher;

use Exception;
use InvalidArgumentException;
use Kahlan\Suite;
use Kahlan\Stubber;
use Kahlan\Analysis\Debugger;
use Kahlan\Analysis\Inspector;
use Kahlan\Plugin\Call\Message;
use Kahlan\Plugin\Call\Calls;
use Kahlan\Plugin\Stub;
use Kahlan\Plugin\Monkey;

class ToReceive
{
    /**
     * The messages instance.
     *
     * @var object
     */
    protected $_messages = [];

    /**
     * The expected method method name to be called.
     *
     * @var array
     */
    protected $_expected = [];

    /**
     * The expectation backtrace reference.
     *
     * @var array
     */
    protected $_backtrace = null;

    /**
     * The report.
     *
     * @var array
     */
    protected $_report = [];

    /**
     * The description report.
     *
     * @var array
     */
    protected $_description = [];

    /**
     * Number of occurences to match.
     *
     * @var integer
     */
    protected $_times = 0;

    /**
     * If `true`, will take the calling order into account.
     *
     * @var boolean
     */
    protected $_ordered = false;

    /**
     * Checks that `$actual` receive the `$expected` message.
     *
     * @param  mixed   $actual   The actual value.
     * @param  mixed   $expected The expected message.
     * @return boolean
     */
    public static function match($actual, $expected = null)
    {
        $class = get_called_class();
        $args = func_get_args();
        $actual = array_shift($args);
        return new static($actual, $args);
    }

    /**
     * Constructor
     *
     * @param string|object $actual   A fully-namespaced class name or an object instance.
     * @param string        $expected The expected method method name to be called.
     */
    public function __construct($actual, $expected)
    {
        $this->_backtrace = Debugger::backtrace();

        if (is_string($actual)) {
            $actual = ltrim($actual, '\\');
        }

        $this->_check($actual);

        if (!$expected) {
            throw new InvalidArgumentException("Method name can't be empty.");
        }

        $names = is_array($expected) ? $expected : [$expected];

        $reference = $actual;

        if (count($names) > 1) {
            if (!Stub::registered(Suite::hash($reference))) {
                throw new InvalidArgumentException("Kahlan can't Spy chained methods on real PHP code, you need to Stub the chain first.");
            }
        }

        $reference = $this->_reference($reference);

        foreach ($names as $index => $name) {
            if (preg_match('/^::.*/', $name)) {
                $reference = is_object($reference) ? get_class($reference) : $reference;
            }
            $this->_expected[] = $name;
            $this->_messages[$name] = $this->_watch(new Message([
                'parent'    => $this,
                'reference' => $reference,
                'name'      => $name
            ]));
            $reference = null;
        }
    }

    /**
     * Check the actual value can receive messages.
     *
     * @param mixed $reference An instance or a fully-namespaced class name.
     */
    protected function _check($reference)
    {
        $isString = is_string($reference);
        if ($isString) {
            if (!class_exists($reference)) {
                throw new InvalidArgumentException("Can't Spy the unexisting class `{$reference}`.");
            }
            $reflection = Inspector::inspect($reference);
        } else {
            $reflection = Inspector::inspect(get_class($reference));
        }

        if (!$reflection->isInternal()) {
            return;
        }
        if (!$isString) {
            throw new InvalidArgumentException("Can't Spy built-in PHP instances, create a test double using `Double::instance()`.");
        }
    }

    /**
     * Return the actual reference which must be used.
     *
     * @param mixed $reference An instance or a fully-namespaced class name.
     * @param mixed            The reference or the monkey patched one if exist.
     */
    protected function _reference($reference)
    {
        if (!is_string($reference)) {
            return $reference;
        }

        $pos = strrpos($reference, '\\');
        if ($pos !== false) {
            $namespace = substr($reference, 0, $pos);
            $basename = substr($reference, $pos + 1);
        } else {
            $namespace = null;
            $basename = $reference;
        }
        $substitute = null;
        $reference = Monkey::patched($namespace, $basename, false, $substitute);

        return $substitute ?: $reference;
    }

    /**
     * Watch a message.
     *
     * @param string|object $actual A fully-namespaced class name or an object instance.
     * @param string        $method The expected method method name to be called.
     * @param object                A message instance.
     */
    protected function _watch($message)
    {
        $reference = $message->reference();
        if (!$reference) {
            Suite::register($message->name());
            return $message;
        }
        if (is_object($reference)) {
            Suite::register(get_class($reference));
        }
        Suite::register(Suite::hash($reference));
        return $message;
    }

    /**
     * Sets arguments requirement.
     *
     * @param  mixed ... <0,n> Argument(s).
     * @return self
     */
    public function with()
    {
        $message = end($this->_messages);
        call_user_func_array([$message, 'with'], func_get_args());
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
        foreach ($requirements as $name => $args) {
            if (!isset($this->_messages[$name])) {
                throw new InvalidArgumentException("Unexisting `{$name}` as method as part of the chain definition.");
            }
            if (!is_array($args)) {
                throw new InvalidArgumentException("Argument requirements must be an arrays for `{$name}` method.");
            }
            call_user_func_array([$this->_messages[$name], 'with'], $args);
        }
        return $this;
    }

    /**
     * Sets the number of occurences.
     *
     * @return self
     */
    public function once()
    {
        $this->times(1);
        return $this;
    }

    /**
     * Gets/sets the number of occurences.
     *
     * @param  integer $times The number of occurences to set or none to get it.
     * @return mixed          The number of occurences on get or `self` otherwise.
     */
    public function times($times = null)
    {
        if (!func_num_args()) {
            return $this->_times;
        }
        $this->_times = $times;
        return $this;
    }

    /**
     * Magic getter, if called with `'ordered'` will set ordered to `true`.
     *
     * @param string
     */
    public function __get($name)
    {
        if ($name !== 'ordered') {
            throw new Exception("Unsupported attribute `{$name}` only `ordered` is available.");
        }
        $this->_ordered = true;
        return $this;
    }

    /**
     * Resolves the matching.
     *
     * @return boolean Returns `true` if successfully resolved, `false` otherwise.
     */
    public function resolve()
    {
        $startIndex = $this->_ordered ? Calls::lastFindIndex() : 0;
        $report = Calls::find($this->_messages, $startIndex, $this->times());
        $this->_report = $report;
        $this->_buildDescription($startIndex);
        return $report['success'];
    }

    /**
     * Gets the backtrace reference.
     *
     * @return object
     */
    public function backtrace()
    {
        return $this->_backtrace;
    }

    /**
     * Build the description of the runned `::match()` call.
     *
     * @param mixed $startIndex The startIndex in calls log.
     */
    public function _buildDescription($startIndex = 0)
    {
        $times = $this->times();

        $report = $this->_report;
        $reference = $report['message']->reference();
        $expected = $report['message']->name();
        $with = $report['message']->args();

        $expectedTimes = $times ? ' the expected times' : '';
        $expectedParameters = $with ? ' with expected parameters' : '';

        $this->_description['description'] = "receive the expected method{$expectedParameters}{$expectedTimes}.";

        $calledTimes = count($report['args']);

        if (!$calledTimes) {
            $logged = [];
            foreach (Calls::logs($reference, $startIndex) as $log) {
                $logged[] = $log['static'] ? '::' . $log['name'] : $log['name'];
            }
            $this->_description['data']['actual received calls'] = $logged;
        } elseif ($calledTimes) {
            $this->_description['data']['actual received'] = $expected;
            $this->_description['data']['actual received times'] = $calledTimes;
            if ($with !== null) {
                $this->_description['data']['actual received parameters list'] = $report['args'];
            }
        }

        $this->_description['data']['expected to receive'] = $expected;

        if ($with !== null) {
            $this->_description['data']['expected parameters'] = $with;
        }

        if ($times) {
            $this->_description['data']['expected received times'] = $times;
        }
    }

    /**
     * Returns the description report.
     */
    public function description()
    {
        return $this->_description;
    }
}
