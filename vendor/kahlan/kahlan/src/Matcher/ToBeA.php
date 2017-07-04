<?php
namespace Kahlan\Matcher;

class ToBeA
{
    /**
     * Description reference of the last `::match()` call.
     *
     * @var array
     */
    public static $_description = [];

    /**
     * Checks that `$actual` is of the `$expected` type.
     *
     * @param  mixed   $actual   The actual value.
     * @param  mixed   $expected The expected value.
     * @return boolean
     */
    public static function match($actual, $expected)
    {
        $a = static::actual($actual);
        $e = static::expected($expected);

        static::_buildDescription($a, $e);
        return $a === $e;
    }

    /**
     * Normalises the actual value in the expected format.
     *
     * @param  mixed $actual The actual value to be normalized.
     * @return mixed         The normalized value.
     */
    public static function actual($actual)
    {
        return strtolower(gettype($actual));
    }

    /**
     * Normalises the expected value.
     *
     * @param  mixed $expected The expected value to be normalized.
     * @return mixed           The normalized value.
     */
    public static function expected($expected)
    {
        if ($expected === 'bool') {
            $expected = 'boolean';
        }
        if ($expected === 'int') {
            $expected = 'integer';
        }
        if ($expected === 'float') {
            $expected = 'double';
        }
        return strtolower($expected);
    }

    /**
     * Build the description of the runned `::match()` call.
     *
     * @param string $actual   The actual type.
     * @param string $expected The expected type.
     */
    public static function _buildDescription($actual, $expected)
    {
        $description = "have the expected type.";
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
