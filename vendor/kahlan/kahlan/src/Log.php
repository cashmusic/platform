<?php
namespace Kahlan;

use Kahlan\Analysis\Debugger;

class Log
{
    /**
     * The scope context instance.
     *
     * @var object
     */
    protected $_scope = null;

    /**
     * The type of the report.
     *
     * @var object
     */
    protected $_type = null;

    /**
     * The file path related to the report.
     *
     * @var string
     */
    protected $_file = null;

    /**
     * The line related to the report.
     *
     * @var string
     */
    protected $_line = null;

    /**
     * If it's an inverted expectation.
     *
     * @var boolean
     */
    protected $_not = false;

    /**
     * The matcher description result.
     *
     * @var string
     */
    protected $_description = null;

    /**
     * The matcher class name from which this report is related.
     *
     * @var string
     */
    protected $_matcher = null;

    /**
     * The matcher name from which this report is related.
     *
     * @var string
     */
    protected $_matcherName = null;

    /**
     * The matcher data.
     *
     * @var array
     */
    protected $_data = [];

    /**
     * The related exception.
     *
     * @var string
     */
    protected $_exception = null;

    /**
     * The backtrace.
     *
     * @var array
     */
    protected $_backtrace = [];

    /**
     * The reports of executed expectations.
     *
     * @var array
     */
    protected $_children = [];

    /**
     * The Constructor.
     *
     * @param array $config The Suite config array. Options are:
     *                      -`'scope'` _object_: the scope context instance.
     */
    public function __construct($config = [])
    {
        $defaults = [
            'scope'       => null,
            'type'        => 'passed',
            'not'         => false,
            'description' => null,
            'matcher'     => null,
            'matcherName' => null,
            'data'        => [],
            'backtrace'   => [],
            'exception'   => null
        ];
        $config += $defaults;

        $this->_scope       = $config['scope'];
        $this->_type        = $config['type'];
        $this->_not         = $config['not'];
        $this->_description = $config['description'];
        $this->_matcher     = $config['matcher'];
        $this->_matcherName = $config['matcherName'];
        $this->_data        = $config['data'];
        $this->exception($config['exception']);
        if ($config['backtrace']) {
            $this->backtrace($config['backtrace']);
        } elseif ($this->scope()) {
            $this->backtrace($this->scope()->backtrace());
        }
    }

    /**
     * Gets the scope context of the report.
     *
     * @return object
     */
    public function scope()
    {
        return $this->_scope;
    }

    /**
     * Gets the type of the report.
     *
     * @return string
     */
    public function type($type = null)
    {
        if (!func_num_args()) {
            return $this->_type;
        }
        $this->_type = $type;
        return $this;
    }

    /**
     * Return the state of the log.
     *
     * @return boolean
     */
    public function passed()
    {
        return $this->_type !== 'failed' && $this->_type !== 'errored';
    }

    /**
     * Gets the not boolean.
     *
     * @return string
     */
    public function not()
    {
        return $this->_not;
    }

    /**
     * Gets the matcher description result.
     *
     * @return string
     */
    public function description()
    {
        return $this->_description;
    }

    /**
     * Gets the matcher class name related to the report.
     *
     * @return string
     */
    public function matcher()
    {
        return $this->_matcher;
    }

    /**
     * Gets the matcher name related to the report.
     *
     * @return string
     */
    public function matcherName()
    {
        return $this->_matcherName;
    }

    /**
     * Gets the matcher data.
     *
     * @return array
     */
    public function data()
    {
        return $this->_data;
    }

    /**
     * Gets the exception related to the report.
     *
     * @return object
     */
    public function exception($exception = null)
    {
        if (!func_num_args()) {
            return $this->_exception;
        }
        $this->_exception = $exception;
        return $this;
    }

    /**
     * Gets the backtrace related to the report.
     *
     * @return array
     */
    public function backtrace($backtrace = [])
    {
        if (!func_num_args()) {
            return $this->_backtrace;
        }
        if ($this->_backtrace = $backtrace) {
            $trace = reset($this->_backtrace);
            $this->_file = preg_replace('~' . preg_quote(getcwd(), '~') . '~', '', '.' . $trace['file']);
            $this->_line = $trace['line'];
        }
        return $this;
    }

    /**
     * Gets file path related to the report.
     *
     * @return array
     */
    public function file()
    {
        return $this->_file;
    }

    /**
     * Gets line related to the report.
     *
     * @return array
     */
    public function line()
    {
        return $this->_line;
    }

    /**
     * Gets the scope related messages.
     *
     * @return array
     */
    public function messages()
    {
        return $this->scope()->messages();
    }

    /**
     * Gets all executed expectations reports.
     *
     * @return array The executed expectations reports.
     */
    public function children()
    {
        return $this->_children;
    }

    /**
     * Adds an expectation report and emits a report event.
     *
     * @param array $data The report data.
     */
    public function add($type, $data = [])
    {
        if ($this->type() === 'passed' && $type === 'failed') {
            $this->type('failed');
        }
        $data['type'] = $type;
        if (!isset($data['backtrace'])) {
            $data['backtrace'] = [];
        } else {
            $data['backtrace'] = Debugger::focus($this->scope()->backtraceFocus(), $data['backtrace'], 1);
        }
        $child = new static($data + ['scope' => $this->_scope]);
        $this->_children[] = $child;
        return $child;
    }
}
