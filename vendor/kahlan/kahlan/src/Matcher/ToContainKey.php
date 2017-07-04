<?php
namespace Kahlan\Matcher;

use Traversable;
use ArrayAccess;

class ToContainKey
{
    /**
     * Expect that `$actual` array contain the `$expected` key.
     *
     * @param  collection $actual The actual array.
     * @param  mixed      $expected The expected key.
     * @return boolean
     */
    public static function match($actual, $expected)
    {
        $args = func_get_args();
        $expected = count($args) > 2 ? array_slice($args, 1) : $expected;
        $expected = (array) $expected;

        if (is_array($actual)) {
            foreach ($expected as $key) {
                if (!array_key_exists($key, $actual)) {
                    return false;
                }
            }
            return true;
        } elseif ($actual instanceof ArrayAccess) {
            foreach ($expected as $key) {
                if (!isset($actual[$key])) {
                    return false;
                }
            }
            return true;
        } elseif ($actual instanceof Traversable) {
            foreach ($expected as $key) {
                foreach ($actual as $k => $v) {
                    if ($key === $k) {
                        continue 2;
                    }
                }
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Returns the description message.
     *
     * @return string The description message.
     */
    public static function description()
    {
        return "contain expected key.";
    }
}
