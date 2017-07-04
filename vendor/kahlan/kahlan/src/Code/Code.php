<?php
namespace Kahlan\Code;

use Exception;
use InvalidArgumentException;

class Code
{
    /**
     * Executes a callable until a timeout is reached or the callable returns `true`.
     *
     * @param  Callable $callable The callable to execute.
     * @param  integer  $timeout  The timeout value.
     * @return mixed
     */
    public static function run($callable, $timeout = 0)
    {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException();
        }

        $timeout = (integer) $timeout;

        if (!function_exists('pcntl_signal')) {
            throw new Exception("PCNTL threading is not supported by your system.");
        }

        pcntl_signal(SIGALRM, function ($signal) use ($timeout) {
            throw new TimeoutException("Timeout reached, execution aborted after {$timeout} second(s).");
        }, true);

        pcntl_alarm($timeout);

        $result = null;

        try {
            $result = $callable();
            pcntl_alarm(0);
        } catch (Exception $e) {
            pcntl_alarm(0);
            throw $e;
        }

        return $result;
    }

    /**
     * Executes a callable in a loop until a timeout is reached or the callable returns `true`.
     *
     * @param  Callable $callable The callable to execute.
     * @param  integer  $timeout  The timeout value.
     * @return mixed
     */
    public static function spin($callable, $timeout = 0, $delay = 100000)
    {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException();
        }

        $closure = function () use ($callable, $timeout, $delay) {
            $timeout = (float) $timeout;
            $result = false;
            $start = microtime(true);

            do {
                if ($result = $callable()) {
                    return $result;
                }
                usleep($delay);
                $current = microtime(true);
            } while ($current - $start < $timeout);

            throw new TimeoutException("Timeout reached, execution aborted after {$timeout} second(s).");
        };

        if (!function_exists('pcntl_signal')) {
            return $closure();
        }
        return static::run($closure, $timeout);
    }
}
