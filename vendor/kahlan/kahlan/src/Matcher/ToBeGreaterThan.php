<?php
namespace Kahlan\Matcher;

class ToBeGreaterThan
{
    /**
     * Expect that `$actual` is greater than `$expected`.
     *
     * @param  mixed   $actual The actual value.
     * @param  mixed   $expected The expected value.
     * @return boolean
     */
    public static function match($actual, $expected)
    {
        return $actual > $expected;
    }

    /**
     * Returns the description message.
     *
     * @return string The description message.
     */
    public static function description()
    {
        return "be greater than expected.";
    }
}
