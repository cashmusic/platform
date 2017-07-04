<?php
namespace Kahlan;

use Closure;
use Exception;
use Kahlan\Analysis\Debugger;
use Kahlan\Plugin\Call\Message;

class Scope
{
    /**
     * Indicates whether the scope has been runned or not.
     *
     * @var boolean
     */
    protected $_runned = false;

    /**
     * Stores the success value.
     *
     * @var boolean
     */
    protected $_passed = true;

    /**
     * Instances stack.
     *
     * @var Scope[]
     */
    protected static $_instances = [];

    /**
     * List of reserved keywords which can't be used as scope variable.
     *
     * @var array
     */
    public static $blacklist = [
        '__construct' => true,
        '__call'      => true,
        '__get'       => true,
        '__set'       => true,
        'after'       => true,
        'afterEach'   => true,
        'before'      => true,
        'beforeEach'  => true,
        'context'     => true,
        'current'     => true,
        'describe'    => true,
        'excluded'    => true,
        'expect'      => true,
        'focused'     => true,
        'failfast'    => true,
        'given'       => true,
        'hash'        => true,
        'it'          => true,
        'log'         => true,
        'logs'        => true,
        'matcher'     => true,
        'message'     => true,
        'messages'    => true,
        'passed'      => true,
        'process'     => true,
        'register'    => true,
        'registered'  => true,
        'report'      => true,
        'reset'       => true,
        'run'         => true,
        'skipIf'      => true,
        'status'      => true,
        'summary'     => true,
        'timeout'     => true,
        'type'        => true,
        'wait'        => true,
        'fdescribe'   => true,
        'fcontext'    => true,
        'fit'         => true,
        'xdescribe'   => true,
        'xcontext'    => true,
        'xit'         => true
    ];

    /**
     * The scope type.
     *
     * @var object
     */
    protected $_type = null;

    /**
     * The root instance.
     *
     * @var object
     */
    protected $_root = null;

    /**
     * The parent instance.
     *
     * @var Scope
     */
    protected $_parent = null;

    /**
     * The spec message.
     *
     * @var string
     */
    protected $_message = null;

    /**
     * The spec closure.
     *
     * @var Closure
     */
    protected $_closure = null;

    /**
     * The scope's data.
     *
     * @var array
     */
    protected $_data = [];

    /**
     * The lazy loaded scope's data.
     *
     * @var array
     */
    protected $_given = [];

    /**
     * The report log of executed spec.
     *
     * @var object
     */
    protected $_log = null;

    /**
     * The execution summary instance.
     *
     * @var object
     */
    protected $_summary = null;

    /**
     * Count the number of failure or exception.
     *
     * @see ::failfast()
     * @var integer
     */
    protected $_failures = 0;

    /**
     * The reporters container.
     *
     * @var object
     */
    protected $_reporters = null;

    /**
     * Boolean lock which avoid `process()` to be called in tests
     */
    protected $_locked = false;

    /**
     * The timeout value.
     *
     * @var integer
     */
    protected $_timeout = 0;

    /**
     * A regexp pattern used to removes useless traces to focus on the one
     * related to a spec file.
     *
     * @var string
     */
    protected $_backtraceFocus = null;

    /**
     * The scope backtrace.
     *
     * @var object
     */
    protected $_backtrace = null;

    /**
     * The Constructor.
     *
     * @param array $config The Suite config array. Options are:
     *                       -`'type'`    _string_ : supported type are `'normal'` & `'focus'`.
     *                       -`'message'` _string_ : the description message.
     *                       -`'parent'`  _object_ : the parent scope.
     *                       -`'root'`    _object_ : the root scope.
     *                       -`'log'`     _object_ : the log instance.
     *                       -`'timeout'` _integer_: the timeout.
     */
    public function __construct($config = [])
    {
        $defaults = [
            'type'    => 'normal',
            'message' => '',
            'parent'  => null,
            'root'    => null,
            'log'     => null,
            'timeout' => 0,
            'summary' => null
        ];
        $config += $defaults;

        $this->_type      = $config['type'];
        $this->_message   = $config['message'];
        $this->_parent    = $config['parent'];
        $this->_root      = $this->_parent ? $this->_parent->_root : $this;
        $this->_timeout   = $config['timeout'];
        $this->_backtrace = Debugger::focus($this->backtraceFocus(), Debugger::backtrace(), 1);
        $this->_log       = $config['log'] ?: new Log([
            'scope' => $this,
            'backtrace' => $this->_backtrace
        ]);
        $this->_summary = $config['summary'];
        if ($this->_summary) {
            return;
        }
        if ($this->_root->summary()) {
            $this->_summary = $this->_root->summary();
        } else {
            $this->_summary = new Summary();
        }
    }

    /**
     * Getter.
     *
     * @param  string $key The name of the variable.
     * @return mixed  The value of the variable.
     */
    public function &__get($key)
    {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }
        if (array_key_exists($key, $this->_given)) {
            $scope = static::current();
            $scope->{$key} = $this->_given[$key]($scope);
            return $scope->__get($key);
        }
        if ($this->_parent !== null) {
            return $this->_parent->__get($key);
        }
        if (in_array($key, static::$blacklist)) {
            if ($key === 'expect') {
                throw new Exception("You can't use expect() inside of describe()");
            }
        }
        throw new Exception("Undefined variable `{$key}`.");
    }

    /**
     * Setter.
     *
     * @param  string $key   The name of the variable.
     * @param  mixed  $value The value of the variable.
     * @return mixed  The value of the variable.
     */
    public function __set($key, $value)
    {
        if (isset(static::$blacklist[$key])) {
            throw new Exception("Sorry `{$key}` is a reserved keyword, it can't be used as a scope variable.");
        }
        return $this->_data[$key] = $value;
    }

    /**
     * Allow closures assigned to the scope property to be inkovable.
     *
     * @param  string $name Name of the method being called.
     * @param  array  $args Enumerated array containing the passed arguments.
     * @return mixed
     * @throws Throw an Exception if the property doesn't exists / is not callable.
     */
    public function __call($name, $args)
    {
        $property = null;
        $property = $this->__get($name);

        if (is_callable($property)) {
            return call_user_func_array($property, $args);
        }
        throw new Exception("Uncallable variable `{$name}`.");
    }

    /**
     * Sets a lazy loaded data.
     *
     * @param  string  $name    The lazy loaded variable name.
     * @param  Closure $closure The lazily executed closure.
     * @return object
     */
    public function given($name, $closure)
    {
        if (isset(static::$blacklist[$name])) {
            throw new Exception("Sorry `{$name}` is a reserved keyword, it can't be used as a scope variable.");
        }

        $given = new Given($closure);
        if (array_key_exists($name, $this->_given)) {
            $given->{$name} = $this->_given[$name](static::current());
        }
        $this->_given[$name] = $given;
        return $this;
    }

    /**
     * Gets the parent instance.
     *
     * @return Scope
     */
    public function parent()
    {
        return $this->_parent;
    }

    /**
     * Gets the spec's message.
     *
     * @return array
     */
    public function message()
    {
        return $this->_message;
    }

    /**
     * Gets all messages upon the root.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [];
        $instances = $this->_parents(true);
        foreach ($instances as $instance) {
            $messages[] = $instance->message();
        }
        return $messages;
    }

    /**
     * Gets the backtrace array.
     *
     * @return array
     */
    public function backtrace()
    {
        return $this->_backtrace;
    }

    /**
     * Skips specs(s) if the condition is `true`.
     *
     * @param boolean $condition
     * @throws SkipException
     */
    public function skipIf($condition)
    {
        if (!$condition) {
            return;
        }
        $exception = new SkipException();
        throw $exception;
    }

    /**
     * Skips children specs(s).
     *
     * @param object  $exception The exception at the origin of the skip.
     * @param boolean $emit      Indicated if report events should be generated.
     */
    protected function _skipChildren($exception, $emit = false)
    {
        $log = $this->log();
        if ($this instanceof Suite) {
            foreach ($this->children() as $child) {
                $child->_skipChildren($exception, true);
            }
        } elseif ($emit) {
            if (!$this->_root->focused() || $this->focused()) {
                $this->report('specStart', $this);
                $this->_passed = true;
                $this->log()->type('skipped');
                $this->summary()->log($this->log());
                $this->report('specEnd', $log);
            }
        } else {
            $this->_passed = true;
            $this->log()->type('skipped');
        }
    }

    /**
     * Manages catched exception.
     *
     * @param Exception $exception  The catched exception.
     * @param boolean   $inEachHook Indicates if the exception occurs in a beforeEach/afterEach hook.
     */
    protected function _exception($exception, $inEachHook = false)
    {
        switch (get_class($exception)) {
            case 'Kahlan\SkipException':
                if ($inEachHook) {
                    $this->log()->type('skipped');
                } else {
                    $this->_skipChildren($exception);
                }
                break;
            default:
                $this->_passed = false;
                $this->log()->type('errored');
                $this->log()->exception($exception);
                break;
        }
    }

    /**
     * Gets all parent instances.
     *
     * @param  boolean $current If `true` include `$this` to the list.
     * @return array.
     */
    protected function _parents($current = false)
    {
        $instances = [];
        $instance  = $current ? $this : $this->_parent;

        while ($instance !== null) {
            $instances[] = $instance;
            $instance = $instance->_parent;
        }
        return array_reverse($instances);
    }

    /**
     * Binds the closure to the current context.
     *
     * @param  Closure $closure The variable to check
     * @param  string  $name    Name of the parent type (TODO: to use somewhere).
     *
     * @return Closure
     * @throws Exception Throw an Exception if the passed parameter is not a closure
     */
    protected function _bind($closure, $name)
    {
        if (!is_callable($closure)) {
            throw new Exception("Error, invalid closure.");
        }
        return @$closure->bindTo($this);
    }

    /**
     * Gets/sets the regexp pattern used to removes useless traces to focus on the one
     * related to a spec file.
     *
     * @param  string $pattern A wildcard pattern (i.e. `fnmatch()` style).
     * @return string          The focus regexp.
     */
    public function backtraceFocus($pattern = null)
    {
        if ($pattern === null) {
            return $this->_root->_backtraceFocus;
        }
        $patterns = is_array($pattern) ? $pattern : [$pattern];
        foreach ($patterns as $key => $value) {
            $patterns[$key] = preg_quote($value, '~');
        }
        $pattern = join('|', $patterns);
        return $this->_root->_backtraceFocus = strtr($pattern, ['\*' => '.*', '\?' => '.']);
    }

    /**
     * Set/get the scope type.
     *
     * @param  string  The type mode.
     * @return mixed
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
     * Check for excluded mode.
     *
     * @return boolean
     */
    public function excluded()
    {
        return $this->_type === 'exclude';
    }

    /**
     * Check for focused mode.
     *
     * @return boolean
     */
    public function focused()
    {
        return $this->_type === 'focus';
    }

    /**
     * Applies focus up to the root.
     */
    protected function _emitFocus()
    {
        $this->_root->summary()->add('focused', $this);
        $instances = $this->_parents(true);

        foreach ($instances as $instance) {
            $instance->type('focus');
        }
    }

    /**
     * Gets specs excecution results.
     *
     * @return array
     */
    public function summary()
    {
        return $this->_root->_summary;
    }

    /**
     * Get the active scope instance.
     *
     * @return Scope The object instance or `null` if there's no active instance.
     */
    public static function current()
    {
        return end(static::$_instances);
    }

    /**
     * Dispatches a report up to the root scope.
     * It only logs expectations report.
     *
     * @param object $log The report object to log.
     */
    public function log($type = null, $data = [])
    {
        if (!func_num_args()) {
            return $this->_log;
        }
        $this->report($type, $this->log()->add($type, $data));
    }

    /**
     * Send some data to reporters.
     *
     * @param string $type The message type.
     * @param mixed  $data The message data.
     */
    public function report($type, $data, $byPassFocuses = false)
    {
        if (!$this->_root->_reporters) {
            return;
        }
        if (!$byPassFocuses && $this->_root->focused() && !$this->focused()) {
            return;
        }
        $this->_root->_reporters->dispatch($type, $data);
    }

    /**
     * Gets the reporters container.
     *
     * @return object
     */
    public function reporters()
    {
        return $this->_root->_reporters;
    }

    /**
     * Gets/sets the timeout.
     *
     * @return integer
     */
    public function timeout($timeout = null)
    {
        if (func_num_args()) {
            $this->_timeout = $timeout;
        }
        return $this->_timeout;
    }
}
