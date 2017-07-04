<?php
namespace Kahlan\Matcher;

use Exception;
use Kahlan\Suite;
use Kahlan\Analysis\Debugger;
use Kahlan\Plugin\Monkey;
use Kahlan\Plugin\Call\Message;
use Kahlan\Plugin\Call\Calls;

class ToBeCalled
{
    /**
     * A fully-namespaced function name.
     *
     * @var string|object
     */
    protected $_actual = null;

    /**
     * The expectation backtrace reference.
     *
     * @var array
     */
    protected $_backtrace = null;

    /**
     * The message instance.
     *
     * @var object
     */
    protected $_message = null;

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
     * Checks that `$actual` will be called.
     *
     * @param  mixed   $actual   The actual value.
     * @param  mixed   $expected Unused.
     * @return boolean
     */
    public static function match($actual)
    {
        return new static($actual);
    }

    /**
     * Constructor
     *
     * @param string|object $actual   A fully-namespaced class name or an object instance.
     * @param string        $expected The expected method method name to be called.
     */
    public function __construct($actual)
    {
        $actual = ltrim($actual, '\\');
        $this->_actual = $actual;
        Suite::register(Suite::hash($actual));
        $this->_message = new Message(['name' => $actual]);
        $this->_backtrace = Debugger::backtrace();
    }

    /**
     * Sets arguments requirement.
     *
     * @param  mixed ... <0,n> Argument(s).
     * @return self
     */
    public function with()
    {
        call_user_func_array([$this->_message, 'with'], func_get_args());
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
        $report = Calls::find($this->_message, $startIndex, $this->times());
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
        $with = $this->_message->args();
        $times = $this->times();

        $report = $this->_report;

        $expectedTimes = $times ? ' the expected times' : '';
        $expectedParameters = $with ? ' with expected parameters' : '';

        $this->_description['description'] = "be called{$expectedParameters}{$expectedTimes}.";

        $calledTimes = count($report['args']);

        $this->_description['data']['actual'] = $this->_actual . '()';
        $this->_description['data']['actual called times'] = $calledTimes;

        if ($calledTimes && $with !== null) {
            $this->_description['data']['actual called parameters list'] = $report['args'];
        }

        $this->_description['data']['expected to be called'] = $this->_actual . '()';

        if ($with !== null) {
            $this->_description['data']['expected parameters'] = $with;
        }

        if ($times) {
            $this->_description['data']['expected called times'] = $times;
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
