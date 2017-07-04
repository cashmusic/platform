<?php
namespace Kahlan\Plugin\Call;

use Kahlan\Suite;
use Kahlan\Plugin\Call\Message;

class Calls
{
    /**
     * Logged calls.
     *
     * @var array
     */
    protected static $_logs = [];

    /**
     * Current index of logged calls per reference.
     *
     * @var array
     */
    protected static $_index = 0;

    /**
     * Logs a call.
     *
     * @param mixed  $reference An instance or a fully-namespaced class name or an array of them.
     * @param string $call      The method name.
     */
    public static function log($reference, $call)
    {
        $calls = [];
        if (is_array($reference)) {
            foreach ($reference as $value) {
                $calls[] = static::_call($value, $call);
            }
        } else {
            $calls[] = static::_call($reference, $call);
        }
        static::$_logs[] = $calls;
    }

    /**
     * Helper for the `log()` method.
     *
     * @param object|string $reference An instance or a fully-namespaced class name.
     * @param string $call             The method name.
     */
    public static function _call($reference, $call)
    {
        $static = false;
        if (preg_match('/^::.*/', $call['name'])) {
            $call['name'] = substr($call['name'], 2);
            $call['static'] = true;
        }
        if (is_object($reference)) {
            $call += ['instance' => $reference, 'class' => get_class($reference), 'static' => $static, 'method' => null];
        } elseif ($reference) {
            $call += ['instance' => null, 'class' => $reference, 'static' => $static, 'method' => null];
        } else {
            $call += ['instance' => null, 'class' => null, 'static' => false, 'method' => null];
        }
        return $call;
    }

    /**
     * Get all logs or all logs related to an instance or a fully-namespaced class name.
     *
     * @param  object|string $reference An instance or a fully-namespaced class name.
     * @param  integer      $index      Start index.
     * @return array                    The founded log calls.
     */
    public static function logs($reference = null, $index = 0)
    {
        if (!func_num_args()) {
            return static::$_logs;
        }
        $result = [];
        $count = count(static::$_logs);
        for ($i = $index; $i < $count; $i++) {
            $logs = static::$_logs[$i];
            if ($log = static::_matchReference($reference, $logs)) {
                $result[] = $log;
            }
        }
        return $result;
    }

    /**
     * Gets/sets the find index
     *
     * @param  integer $index The index value to set or `null` to get the current one.
     * @return integer        Return founded log call.
     */
    public static function lastFindIndex($index = null)
    {
        if ($index !== null) {
            static::$_index = $index;
        }
        return static::$_index;
    }

    /**
     * Finds a logged call.
     *
     * @param  object      $message   The message method name.
     * @param  integer     $index     Start index.
     * @param  array       $args      Populated by the list of passed arguments.
     * @return array|false            Return founded log call.
     */
    public static function find($message, $index = 0, $times = 0, &$args = [])
    {
        $success = false;
        $messages = !is_array($message) ? [$message] : $message;

        $message = reset($messages);
        $reference = $message->reference();
        $reference = $message->isStatic() && is_object($reference) ? get_class($reference) : $reference;

        $lastFound = null;

        $count = count(static::$_logs);

        for ($i = $index; $i < $count; $i++) {
            $logs = static::$_logs[$i];
            if (!$log = static::_matchReference($reference, $logs)) {
                continue;
            }

            if (!$message->match($log, false)) {
                continue;
            }
            $args[] = $log['args'];

            if (!$message->matchArgs($log['args'])) {
                continue;
            }

            if ($message = next($messages)) {
                $lastFound = $message;
                if (!$reference = $message->reference() && $log['method']) {
                    $reference = $log['method']->actualReturn();
                }
                if (!is_object($reference)) {
                    $message = reset($messages);
                    $reference = $message->reference();
                }
                $reference = $message->isStatic() && is_object($reference) ? get_class($reference) : $reference;
                continue;
            }

            $times -= 1;
            if ($times < 0) {
                $success = true;
                $next = static::find($messages, $i + 1, 0, $args);
                static::$_index = $i + 1;
                break;
            } elseif ($times === 0) {
                $next = static::find($messages, $i + 1, 0, $args);
                if ($next['success']) {
                    $success = false;
                } else {
                    $success = true;
                    static::$_index = $i + 1;
                }
                break;
            }
            return static::find($messages, $i + 1, $times, $args);
        }
        $index = static::$_index;
        $message = $lastFound ?: reset($messages);
        return compact('success', 'message', 'args', 'index');
    }

    /**
     * Helper for the `_findAll()` method.
     *
     * @param  object|string $reference An instance or a fully-namespaced class name.
     * @param  array         $logs      The logged calls.
     * @return array                    The founded log call.
     */
    protected static function _matchReference($reference, $logs = [])
    {
        foreach ($logs as $log) {
            if (!$reference) {
                if (empty($log['class']) && empty($log['instance'])) {
                    return $log;
                }
            } elseif (is_object($reference)) {
                if ($reference === $log['instance']) {
                    return $log;
                }
            } elseif ($reference === $log['class']) {
                return $log;
            }
        }
    }

    /**
     * Clears the registered references & logs.
     */
    public static function reset()
    {
        static::$_logs = [];
        static::$_index = 0;
        Suite::reset();
    }
}
