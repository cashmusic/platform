<?php
namespace Kahlan\Matcher;

class ToBeTruthy extends ToEqual
{
    /**
     * Expect that `$actual` is truthy.
     *
     * @param  mixed   $actual The actual value.
     * @return boolean
     */
    public static function match($actual, $expected = true)
    {
        return parent::match($actual, true);
    }

    /**
     * Returns the description message.
     *
     * @return string The description message.
     */
    public static function description()
    {
        return "be truthy.";
    }
}
