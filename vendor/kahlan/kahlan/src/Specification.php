<?php
namespace Kahlan;

use Closure;
use Throwable;
use Exception;

class Specification extends Scope
{
    /**
     * List of expectations.
     * @var Expectation[]
     */
    protected $_expectations = [];

    /**
     * Store the return value of the spec closure.
     *
     * @var mixed
     */
    protected $_return = null;

    /**
     * Constructor.
     *
     * @param array $config The Suite config array. Options are:
     *                      -`'closure'` _Closure_ : the closure of the test.
     *                      -`'message'` _string_  : the spec message.
     *                      -`'scope'`   _string_  : supported scope are `'normal'` & `'focus'`.
     */
    public function __construct($config = [])
    {
        $defaults = [
            'closure' => null,
            'message' => 'passes'
        ];
        $config += $defaults;
        $config['message'] = 'it ' . $config['message'];
        parent::__construct($config);

        $config['closure'] = $config['closure'] ?: function () {
        };
        $this->_closure = $this->_bind($config['closure'], 'it');

        if ($this->_type === 'focus') {
            $this->_emitFocus();
        }
    }

    /**
     * The expect statement.
     *
     * @param  Expectation   $actual The expression to check
     *
     * @return Expectation[]
     */
    public function expect($actual, $timeout = -1)
    {
        return $this->_expectations[] = new Expectation(compact('actual', 'timeout'));
    }

    /**
     * The waitsFor statement.
     *
     * @param  Expectation $actual The expression to check
     *
     * @return mixed
     */
    public function waitsFor($actual, $timeout = 0)
    {
        $timeout = $timeout ?: $this->timeout();
        $closure = $actual instanceof Closure ? $actual : function () use ($actual) {
            return $actual;
        };
        $spec = new static(['closure' => $closure]);

        return $this->expect($spec, $timeout);
    }

    /**
     * Processes a child specs.
     *
     * @see Kahlan\Suite::process()
     */
    protected function _process()
    {
        if ($this->_root->focused() && !$this->focused()) {
            return;
        }
        if ($this->excluded()) {
            $this->log()->type('excluded');
            $this->summary()->log($this->log());
            $this->report('specEnd', $this->log());
            return;
        }

        $result = null;

        if (Suite::$PHP >= 7 && !defined('HHVM_VERSION')) {
            try {
                $this->_specStart();
                try {
                    $result = $this->_execute();
                } catch (Throwable $exception) {
                    $this->_exception($exception);
                }
                $this->_specEnd();
            } catch (Throwable $exception) {
                $this->_exception($exception, true);
                $this->_specEnd(!$exception instanceof SkipException);
            }
        } else {
            try {
                $this->_specStart();
                try {
                    $result = $this->_execute();
                } catch (Exception $exception) {
                    $this->_exception($exception);
                }
                $this->_specEnd();
            } catch (Exception $exception) {
                $this->_exception($exception, true);
                $this->_specEnd(!$exception instanceof SkipException);
            }
        }

        return $this->_return = $result;
    }

    /**
     * Processes the spec.
     */
    protected function _execute()
    {
        static::$_instances[] = $this;

        $result = null;

        $spec = function () {
            $this->_expectations = [];
            $closure = $this->_closure;
            $result = $closure($this);
            foreach ($this->_expectations as $expectation) {
                $this->_passed = $expectation->passed() && $this->_passed;
            }
            array_pop(static::$_instances);
            return $result;
        };

        if (Suite::$PHP >= 7 && !defined('HHVM_VERSION')) {
            try {
                $result = $spec();
            } catch (Throwable $e) {
                $this->_passed = false;
                array_pop(static::$_instances);
                throw $e;
            }
        } else {
            try {
                $result = $spec();
            } catch (Exception $e) {
                $this->_passed = false;
                array_pop(static::$_instances);
                throw $e;
            }
        }

        return $result;
    }

    /**
     * Spec start helper.
     */
    protected function _specStart()
    {
        $this->report('specStart', $this);
        if ($this->_parent) {
            $this->_parent->runCallbacks('beforeEach');
        }
    }

    /**
     * Spec end helper.
     */
    protected function _specEnd($runAfterEach = true)
    {
        $type = $this->log()->type();
        foreach ($this->_expectations as $expectation) {
            if (!($logs = $expectation->logs()) && $type !== 'errored') {
                $this->log()->type('pending');
            }
            foreach ($logs as $log) {
                $this->log($log['type'], $log);
            }
        }

        if ($type === 'passed' && !count($this->_expectations)) {
            $this->log()->type('pending');
        }
        $type = $this->log()->type();

        if ($type === 'failed' || $type === 'errored') {
            $this->_root->_failures++;
        }

        if ($this->_parent && $runAfterEach) {
            try {
                $this->_parent->runCallbacks('afterEach');
            } catch (Exception $exception) {
                $this->_exception($exception, true);
            }
        }

        $this->summary()->log($this->log());

        $this->report('specEnd', $this->log());

        if ($this->_parent) {
            $this->_parent->autoclear();
        }

        $currentScope = static::current();
        foreach ($currentScope->_parents(true) as $scope) {
            foreach ($scope->_given as $name => $value) {
                unset($currentScope->_data[$name]);
            }
        }
    }

    /**
     * Checks if all test passed.
     *
     * @return boolean Returns `true` if no error occurred, `false` otherwise.
     */
    public function passed(&$return = null)
    {
        if (!$this->_runned) {
            $this->_process();
        }
        $this->_runned = true;
        $return = $this->_return;
        return $this->_passed;
    }

    /**
     * Returns execution log.
     *
     * @return array
     */
    public function logs()
    {
        $logs = [];
        foreach ($this->_expectations as $expectation) {
            foreach ($expectation->logs() as $log) {
                $logs[] = $log;
            }
        }
        return $logs;
    }
}
