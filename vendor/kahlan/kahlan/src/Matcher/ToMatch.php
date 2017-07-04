<?php
namespace Kahlan\Matcher;

class ToMatch extends ToEqual
{
    /**
     * Expect that `$actual` match the `$expected` pattern.
     *
     * @param  mixed   $actual   The actual value.
     * @param  mixed   $expected The expected regexp pattern or the closure to use
     *                           for the matching.
     * @return boolean
     */
    public static function match($actual, $expected)
    {
        if (is_callable($expected)) {
            return $expected($actual);
        }
        $actual = static::_nl($actual);
        return !!preg_match($expected, $actual);
    }

    /**
     * Returns the description message.
     *
     * @return string The description message.
     */
    public static function description()
    {
        return "match expected.";
    }
}
