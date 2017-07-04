<?php
namespace Kahlan\Matcher;

use Throwable;
use Exception;

class ToThrow
{
    /**
     * Description reference of the last `::match()` call.
     *
     * @var array
     */
    public static $_description = [];

    /**
     * Checks that `$actual` throws the `$expected` exception.
     *
     * The value passed to `$expected` is either an exception or the expected exception's message.
     *
     * @param  Closure $actual   The closure to run.
     * @param  mixed   $expected A string indicating what the error text is expected to be or a
     *                           exception instance.
     * @param  integer $code     The expected `Exception` code if `$expected` is a string.
     * @return boolean
     */
    public static function match($actual, $expected = null, $code = 0)
    {
        $exception = static::expected($expected, $code);
        $a = static::actual($actual);

        static::_buildDescription($a, $exception);
        return static::_matchException($a, $exception);
    }

    /**
     * Normalise the actual value as an Exception.
     *
     * @param  mixed $actual The actual value to be normalized.
     * @return mixed         The normalised value.
     */
    public static function actual($actual)
    {
        try {
            $actual();
        } catch (Exception $e) { // PHP<7 compat
            return $e;
        } catch (Throwable $e) {
            return $e;
        }
    }

    /**
     * Normalise the expected value as an Exception.
     *
     * @param  mixed   $expected The expected value to be normalized.
     * @param  integer $code     The expected `Exception` code if `$expected` is a string.
     * @return mixed             The normalised value.
     */
    public static function expected($expected, $code = 0)
    {
        if ($expected === null || is_string($expected)) {
            return new AnyException($expected, $code);
        }
        return $expected;
    }

    /**
     * Compares if two exception are similar.
     *
     * @param  object $actual   The actual instance.
     * @param  object $expected The expected instance.
     * @return boolean
     */
    public static function _matchException($actual, $exception)
    {
        if (!$actual) {
            return false;
        }
        if ($exception instanceof AnyException) {
            $code = $exception->getCode() ? $actual->getCode() : $exception->getCode();
            $class = get_class($actual);
        } else {
            $code = $actual->getCode();
            $class = get_class($exception);
        }

        if (get_class($actual) !== $class) {
            return false;
        }

        $sameCode = $code === $exception->getCode();
        $sameMessage = static::_sameMessage($actual->getMessage(), $exception->getMessage());
        return $sameCode && $sameMessage;
    }

    /**
     * Compare if exception messages are similar.
     *
     * @param  string  $actual   The actual message.
     * @param  string  $expected The expected message.
     * @return boolean
     */
    public static function _sameMessage($actual, $expected)
    {
        if (preg_match('~^(?P<char>\~|/|@|#).*?(?P=char)$~', (string) $expected)) {
            $same = preg_match($expected, $actual);
        } else {
            $same = $actual === $expected;
        }
        return $same || !$expected;
    }

    /**
     * Build the description of the runned `::match()` call.
     *
     * @param object $actual   The actual exception instance.
     * @param object $expected The expected exception instance.
     */
    public static function _buildDescription($actual, $expected)
    {
        $description = "throw a compatible exception.";
        $data['actual'] = $actual;
        $data['expected'] = $expected;

        static::$_description = compact('description', 'data');
    }

    /**
     * Returns the description report.
     */
    public static function description()
    {
        return static::$_description;
    }
}
