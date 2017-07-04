<?php
namespace Kahlan\Reporter;

class Reporter
{
    /**
     * Starting time.
     *
     * @var float
     */
    protected $_start = 0;

    /**
     * Total of items to reach.
     *
     * @var integer
     */
    protected $_total = 0;

    /**
     * Current position.
     *
     * @var integer
     */
    protected $_current = 0;

    /**
     * The Constructor.
     *
     * @param array $config The config array. Possible values are:
     *                      - `'start' _integer_: A microtime value.
     */
    public function __construct($config = [])
    {
        $defaults = ['start' => microtime(true)];
        $config += $defaults;
        $this->_start = $config['start'];
    }

    /**
     * Callback called before any specs processing.
     *
     * @param array $args The suite arguments.
     */
    public function start($args)
    {
        $this->_start = $this->_start ?: microtime(true);
        $this->_total = max(1, $args['total']);
    }

    /**
     * Callback called on a suite start.
     *
     * @param object $suite The suite instance.
     */
    public function suiteStart($suite = null)
    {
    }

    /**
     * Callback called on a spec start.
     *
     * @param object $spec The spec object of the whole spec.
     */
    public function specStart($spec = null)
    {
    }

    /**
     * Callback called on successful expect.
     *
     * @param object $log An expect log object.
     */
    public function passed($log = null)
    {
    }

    /**
     * Callback called on failure.
     *
     * @param object $log An expect log object.
     */
    public function failed($log = null)
    {
    }

    /**
     * Callback called after a spec execution.
     *
     * @param object $log The log object of the whole spec.
     */
    public function specEnd($log = null)
    {
        $this->_current++;
    }

    /**
     * Callback called after a suite execution.
     *
     * @param object $suite The suite instance.
     */
    public function suiteEnd($suite = null)
    {
    }

    /**
     * Callback called at the end of specs processing.
     *
     * @param object $summary The execution summary instance.
     */
    public function end($summary)
    {
    }

    /**
     * Callback called at the end of the process.
     *
     * @param object $summary The execution summary instance.
     */
    public function stop($summary)
    {
    }
}
