<?php
namespace Kahlan;

use Closure;
use Throwable;
use Exception;
use InvalidArgumentException;
use Kahlan\Analysis\Debugger;

class Suite extends Scope
{
    public static $PHP = PHP_MAJOR_VERSION;

    /**
     * Store all hashed references.
     *
     * @var array
     */
    protected static $_registered = [];

    /**
     * The return status value (`0` for success).
     *
     * @var integer
     */
    protected $_status = null;

    /**
     * The children array.
     *
     * @var Suite[]|Specification[]
     */
    protected $_children = [];

    /**
     * Suite statistics.
     *
     * @var array
     */
    protected $_stats = null;

    /**
     * The each callbacks.
     *
     * @var array
     */
    protected $_callbacks = [
        'beforeAll'  => [],
        'afterAll'   => [],
        'beforeEach' => [],
        'afterEach'  => [],
    ];

    /**
     * Array of fully-namespaced class name to clear on each `it()`.
     *
     * @var array
     */
    protected $_autoclear = [];

    /**
     * Set the number of fails allowed before aborting. `0` mean no fast fail.
     *
     * @see ::failfast()
     * @var integer
     */
    protected $_ff = 0;

    /**
     * The Constructor.
     *
     * @param array $config The Suite config array. Options are:
     *                      -`'closure'` _Closure_: the closure of the test.
     *                      -`'name'`    _string_ : the type of the suite.
     */
    public function __construct($config = [])
    {
        $defaults = [
            'closure' => null,
            'name'    => 'describe'
        ];
        $config += $defaults;
        parent::__construct($config);

        if ($this->_root === $this) {
            return;
        }
        $closure = $this->_bind($config['closure'], $config['name']);
        $this->_closure = $closure;
        if ($this->_type === 'focus') {
            $this->_emitFocus();
        }
    }

    /**
     * Adds a group/class related spec.
     *
     * @param  string  $message Description message.
     * @param  Closure $closure A test case closure.
     *
     * @return Suite
     */
    public function describe($message, $closure, $timeout = null, $type = 'normal')
    {
        $parent = $this;
        $name = 'describe';
        $timeout = $timeout !== null ? $timeout : $this->timeout();
        $suite = new Suite(compact('message', 'closure', 'parent', 'name', 'timeout', 'type'));

        return $this->_children[] = $suite;
    }

    /**
     * Adds a context related spec.
     *
     * @param  string  $message Description message.
     * @param  Closure $closure A test case closure.
     * @param  null    $timeout
     * @param  string  $type
     *
     * @return Suite
     */
    public function context($message, $closure, $timeout = null, $type = 'normal')
    {
        $parent = $this;
        $name = 'context';
        $timeout = $timeout !== null ? $timeout : $this->timeout();
        $suite = new Suite(compact('message', 'closure', 'parent', 'name', 'timeout', 'type'));

        return $this->_children[] = $suite;
    }

    /**
     * Adds a spec.
     *
     * @param  string|Closure $message Description message or a test closure.
     * @param  Closure        $closure A test case closure.
     * @param  string         $type   The type.
     *
     * @return Specification
     */
    public function it($message, $closure = null, $timeout = null, $type = 'normal')
    {
        static $inc = 1;
        if ($message instanceof Closure) {
            $type = $timeout;
            $timeout = $closure;
            $closure = $message;
            $message = "spec #" . $inc++;
        }
        $parent = $this;
        $root = $this->_root;
        $timeout = $timeout !== null ? $timeout : $this->timeout();
        $spec = new Specification(compact('message', 'closure', 'parent', 'root', 'timeout', 'type'));
        $this->_children[] = $spec;

        return $this;
    }

    /**
     * Comments out a group/class related spec.
     *
     * @param  string  $message Description message.
     * @param  Closure $closure A test case closure.
     *
     * @return
     */
    public function xdescribe($message, $closure, $timeout = null)
    {
        return $this->describe($message, $closure, $timeout, 'exclude');
    }

    /**
     * Comments out a context related spec.
     *
     * @param  string  $message Description message.
     * @param  Closure $closure A test case closure.
     *
     * @return
     */
    public function xcontext($message, $closure, $timeout = null)
    {
        return $this->context($message, $closure, $timeout, 'exclude');
    }

    /**
     * Comments out a spec.
     *
     * @param  string|Closure $message Description message or a test closure.
     * @param  Closure|null   $closure A test case closure or `null`.
     *
     * @return
     */
    public function xit($message, $closure = null, $timeout = null)
    {
        return $this->it($message, $closure, $timeout, 'exclude');
    }

    /**
     * Adds an focused group/class related spec.
     *
     * @param  string  $message Description message.
     * @param  Closure $closure A test case closure.
     *
     * @return Suite
     */
    public function fdescribe($message, $closure, $timeout = null)
    {
        return $this->describe($message, $closure, $timeout, 'focus');
    }

    /**
     * Adds an focused context related spec.
     *
     * @param  string  $message Description message.
     * @param  Closure $closure A test case closure.
     *
     * @return Suite
     */
    public function fcontext($message, $closure, $timeout = null)
    {
        return $this->context($message, $closure, $timeout, 'focus');
    }

    /**
     * Adds an focused spec.
     *
     * @param  string|Closure $message Description message or a test closure.
     * @param  Closure|null   $closure A test case closure or `null`.
     *
     * @return Specification
     */
    public function fit($message, $closure = null, $timeout = null)
    {
        return $this->it($message, $closure, $timeout, 'focus');
    }

    /**
     * Executed before tests.
     *
     * @param  Closure $closure A closure
     *
     * @return self
     */
    public function beforeAll($closure)
    {
        $this->_bind($closure, 'beforeAll');
        $this->_callbacks['beforeAll'][] = $closure;

        return $this;
    }

    /**
     * Executed after tests.
     *
     * @param  Closure $closure A closure
     *
     * @return self
     */
    public function afterAll($closure)
    {
        $this->_bind($closure, 'afterAll');
        $this->_callbacks['afterAll'][] = $closure;

        return $this;
    }

    /**
     * Executed before each tests.
     *
     * @param  Closure $closure A closure
     *
     * @return self
     */
    public function beforeEach($closure)
    {
        $this->_bind($closure, 'beforeEach');
        $this->_callbacks['beforeEach'][] = $closure;

        return $this;
    }

    /**
     * Executed after each tests.
     *
     * @param  Closure $closure A closure
     *
     * @return self
     */
    public function afterEach($closure)
    {
        $this->_bind($closure, 'afterEach');
        $this->_callbacks['afterEach'][] = $closure;

        return $this;
    }

    /**
     * Suite processing.
     *
     * @param array $options Process options.
     */
    protected function _process($options = [])
    {
        static::$_instances[] = $this;
        $this->_errorHandler(true, $options);

        $suite = function () {
            $this->_suiteStart();
            foreach ($this->_children as $child) {
                if ($this->failfast()) {
                    break;
                }
                $this->_passed = $child->passed() && $this->_passed;
            }
            $this->_suiteEnd();
        };

        if (Suite::$PHP >= 7 && !defined('HHVM_VERSION')) {
            try {
                $suite();
            } catch (Throwable $exception) {
                $this->_exception($exception);
                $this->_suiteEnd();
            }
        } else {
            try {
                $suite();
            } catch (Exception $exception) {
                $this->_exception($exception);
                $this->_suiteEnd();
            }
        }

        $this->_errorHandler(false);
        array_pop(static::$_instances);
    }

    /**
     * Suite start helper.
     */
    protected function _suiteStart()
    {
        if ($this->message()) {
            $this->report('suiteStart', $this);
        }
        $this->runCallbacks('beforeAll', false);
    }

    /**
     * Suite end helper.
     */
    protected function _suiteEnd()
    {
        if (Suite::$PHP >= 7 && !defined('HHVM_VERSION')) {
            try {
                $this->runCallbacks('afterAll', false);
            } catch (Throwable $exception) {
                $this->_exception($exception);
            }
        } else {
            try {
                $this->runCallbacks('afterAll', false);
            } catch (Exception $exception) {
                $this->_exception($exception);
            }
        }

        $type = $this->log()->type();
        if ($type === 'failed' || $type === 'errored') {
            $this->_root->_failures++;
            $this->summary()->log($this->log());
        }

        if ($this->message()) {
            $this->report('suiteEnd', $this);
        }
    }

    /**
     * Returns `true` if the suite reach the number of allowed failure by the fail-fast parameter.
     *
     * @return boolean;
     */
    public function failfast()
    {
        return $this->_root->_ff && $this->_root->_failures >= $this->_root->_ff;
    }

    /**
     * Runs a callback.
     *
     * @param string $name The name of the callback (i.e `'beforeEach'` or `'afterEach'`).
     */
    public function runCallbacks($name, $recursive = true)
    {
        $instances = $recursive ? $this->_parents(true) : [$this];
        foreach ($instances as $instance) {
            foreach ($instance->_callbacks[$name] as $closure) {
                $closure($this);
            }
        }
    }

    /**
     * Overrides the default error handler
     *
     * @param boolean $enable  If `true` override the default error handler,
     *                         if `false` restore the default handler.
     * @param array   $options An options array. Available options are:
     *                         - 'handler': An error handler closure.
     *
     */
    protected function _errorHandler($enable, $options = [])
    {
        $defaults = ['handler' => null];
        $options += $defaults;
        if (!$enable) {
            return restore_error_handler();
        }
        $handler = function ($code, $message, $file, $line = 0, $args = []) {
            $trace = debug_backtrace();
            $trace = array_slice($trace, 1, count($trace));
            $message = "`" . Debugger::errorType($code) . "` {$message}";
            $code = 0;
            $exception = compact('code', 'message', 'file', 'line', 'trace');
            throw new PhpErrorException($exception);
        };
        $options['handler'] = $options['handler'] ?: $handler;
        set_error_handler($options['handler'], error_reporting());
    }

    /**
     * Runs all specs.
     *
     * @param  array     $options Run options.
     *
     * @return boolean            The result array.
     * @throws Exception
     */
    public function run($options = [])
    {
        $defaults = [
            'reporters' => null,
            'autoclear' => [],
            'ff'        => 0
        ];
        $options += $defaults;

        if ($this->_root->_locked) {
            throw new Exception('Method not allowed in this context.');
        }

        $this->_root->_locked = true;
        $this->_reporters = $options['reporters'];
        $this->_autoclear = (array)$options['autoclear'];
        $this->_ff = $options['ff'];

        $this->report('start', ['total' => $this->enabled()], true);

        $success = $this->passed();
        $this->summary()->memoryUsage(memory_get_peak_usage());

        $this->report('end', $this->summary(), true);

        $this->_root->_locked = false;

        return $success;
    }

    /**
     * Checks if all test passed.
     *
     * @return boolean Returns `true` if no error occurred, `false` otherwise.
     */
    public function passed()
    {
        if (!$this->_runned) {
            $this->_process();
        }
        $this->_runned = true;
        return $this->_passed;
    }

    /**
     * Gets number of total specs.
     *
     * @return integer
     */
    public function total()
    {
        if ($this->_stats === null) {
            $this->stats();
        }
        return $this->_stats['normal'] + $this->_stats['focused'] + $this->_stats['excluded'];
    }

    /**
     * Gets number of enabled specs.
     *
     * @return integer
     */
    public function enabled()
    {
        if ($this->_stats === null) {
            $this->stats();
        }
        return $this->focused() ? $this->_stats['focused'] : $this->_stats['normal'];
    }

    /**
     * Triggers the `stop` event.
     */
    public function stop()
    {
        $this->report('stop', $this->summary(), true);
    }

    /**
     * Builds the suite.
     *
     * @return array The suite stats.
     */
    protected function stats()
    {
        static::$_instances[] = $this;
        if (Suite::$PHP >= 7 && !defined('HHVM_VERSION')) {
            try {
                $this->_stats = $this->_stats();
            } catch (Throwable $exception) {
                $this->_exception($exception);

                $this->_stats = [
                    'normal' => 0,
                    'focused' => 0,
                    'excluded' => 0
                ];
            }
        } else {
            try {
                $this->_stats = $this->_stats();
            } catch (Exception $exception) {
                $this->_passed = false;
                array_pop(static::$_instances);
                throw $exception;
            }
        }
        array_pop(static::$_instances);
        return $this->_stats;
    }

    /**
     * Builds the suite.
     *
     * @return array The suite stats.
     */
    protected function _stats()
    {
        if ($closure = $this->_closure) {
            $closure($this);
        }

        $normal = 0;
        $focused = 0;
        $excluded = 0;
        foreach ($this->children() as $child) {
            if ($this->excluded()) {
                $child->type('exclude');
            }
            if ($child instanceof Suite) {
                $result = $child->stats();
                if ($child->focused() && !$result['focused']) {
                    $focused += $result['normal'];
                    $excluded += $result['excluded'];
                    $child->_broadcastFocus();
                } else {
                    $normal += $result['normal'];
                    $focused += $result['focused'];
                    $excluded += $result['excluded'];
                }
            } else {
                switch ($child->type()) {
                    case 'exclude':
                        $excluded++;
                        break;
                    case 'focus':
                        $focused++;
                        break;
                    default:
                        $normal++;
                        break;
                }
            }
        }
        return compact('normal', 'focused', 'excluded');
    }

    /**
     * Gets exit status code according passed results.
     *
     * @param  integer $status If set force a specific status to be retruned.
     *
     * @return boolean         Returns `0` if no error occurred, `-1` otherwise.
     */
    public function status($status = null)
    {
        if (func_num_args()) {
            $this->_status = $status;
            return $this;
        }

        if ($this->focused()) {
            return -1;
        }

        if ($this->_status !== null) {
            return $this->_status;
        }

        return $this->passed() ? 0 : -1;
    }

    /**
     * Gets children.
     *
     * @return array The array of children instances.
     */
    public function children()
    {
        return $this->_children;
    }

    /**
     * Gets callbacks.
     *
     * @param  string $type The type of callbacks to get.
     *
     * @return array        The array callbacks instances.
     */
    public function callbacks($type)
    {
        return isset($this->_callbacks[$type]) ? $this->_callbacks[$type] : [];
    }

    /**
     * Autoclears plugins.
     */
    public function autoclear()
    {
        foreach ($this->_root->_autoclear as $plugin) {
            if (is_object($plugin)) {
                if (method_exists($plugin, 'clear')) {
                    $plugin->clear();
                }
            } elseif (method_exists($plugin, 'reset')) {
                $plugin::reset();
            }
        }
    }

    /**
     * Applies focus downward to the leaf.
     */
    protected function _broadcastFocus()
    {
        foreach ($this->_children as $child) {
            $child->type('focus');
            if ($child instanceof Suite) {
                $child->_broadcastFocus();
            }
        }
    }

    /**
     * Generates a hash from an instance or a string.
     *
     * @param  mixed $reference An instance or a fully namespaced class name.
     *
     * @return string           A string hash.
     * @throws InvalidArgumentException
     */
    public static function hash($reference)
    {
        if (is_object($reference)) {
            return spl_object_hash($reference);
        }
        if (is_string($reference)) {
            return $reference;
        }
        throw new InvalidArgumentException("Error, the passed argument is not hashable.");
    }

    /**
     * Registers a hash. [Mainly used for optimization]
     *
     * @param mixed $hash A hash to register.
     */
    public static function register($hash)
    {
        static::$_registered[$hash] = true;
    }

    /**
     * Gets registered hashes. [Mainly used for optimizations]
     *
     * @param  string     $hash The hash to look up. If none return all registered hashes.
     *
     * @return array|bool
     */
    public static function registered($hash = null)
    {
        if (!func_num_args()) {
            return static::$_registered;
        }

        return isset(static::$_registered[$hash]);
    }

    /**
     * Clears the registered hash.
     */
    public static function reset()
    {
        static::$_registered = [];
    }
}
