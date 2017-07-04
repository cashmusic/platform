<?php
namespace Kahlan\Matcher;

use Countable;

class ToHaveLength
{
    /**
     * Description reference of the last `::match()` call.
     *
     * @var array
     */
    public static $_description = [];

    /**
     * Checks that `$actual` has the `$expected` length.
     *
     * @param  mixed   $actual   The actual value.
     * @param  mixed   $expected The expected value.
     * @return boolean
     */
    public static function match($actual, $expected)
    {
        $length = static::actual($actual);
        static::_buildDescription($actual, $length, $expected);

        return $length === $expected;
    }

    /**
     * Normalize the actual value in the expected format.
     *
     * @param  mixed $actual The actual value to be normalized.
     * @return mixed         The normalized value.
     */
    public static function actual($actual)
    {
        if (is_string($actual)) {
            return strlen($actual);
        } elseif (is_array($actual) || $actual instanceof Countable) {
            return count($actual);
        }
    }

    /**
     * Build the description of the runned `::match()` call.
     *
     * @param mixed   $actual   The actual value.
     * @param mixed   $length   The actual length value value.
     * @param mixed   $expected The expected length value.
     */
    public static function _buildDescription($actual, $length, $expected)
    {
        $description = "have the expected length.";
        $data['actual'] = $actual;
        $data['actual length'] = $length;
        $data['expected length'] = $expected;

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
