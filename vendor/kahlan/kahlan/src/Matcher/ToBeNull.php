<?php
namespace Kahlan\Matcher;

class ToBeNull extends ToBe
{
    /**
     * Expect that `$actual` is `null`.
     *
     * @param  mixed   $actual The actual value.
     * @return boolean
     */
    public static function match($actual, $expected = null)
    {
        return parent::match($actual, null);
    }

    /**
     * Returns the description message.
     *
     * @return string The description message.
     */
    public static function description()
    {
        return "be null.";
    }
}
