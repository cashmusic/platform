<?php
namespace Kahlan\Util;

use Closure;
use Exception;

class Text
{
    /**
     * Replaces variable placeholders inside a string with any given data. Each key
     * in the `$data` array corresponds to a variable placeholder name in `$str`.
     *
     * Usage:
     * {{{
     * Text::insert(
     *     'My name is {:name} and I am {:age} years old.', ['name' => 'Bob', 'age' => '65']
     * );
     * }}}
     *
     * @param  string $str     A string containing variable place-holders.
     * @param  array  $data    A key, value array where each key stands for a place-holder variable
     *                         name to be replaced with value.
     * @param  array  $options Available options are:
     *                         - `'before'`: The character or string in front of the name of the variable
     *                           place-holder (defaults to `'{:'`).
     *                         - `'after'`: The character or string after the name of the variable
     *                           place-holder (defaults to `}`).
     *                         - `'escape'`: The character or string used to escape the before character or string
     *                           (defaults to `'\\'`).
     *                         - `'clean'`: A boolean or array with instructions for `Text::clean()`.
     * @return string
     */
    public static function insert($str, $data, $options = [])
    {
        $options += ['before' => '{:', 'after' => '}', 'escape' => '\\', 'clean' => false];

        extract($options);

        $begin = $escape ? '(?<!' . preg_quote($escape) . ')' . preg_quote($before) : preg_quote($before);
        $end = preg_quote($options['after']);

        foreach ($data as $placeholder => $val) {
            $val = (is_array($val) || is_resource($val) || $val instanceof Closure) ? '' : $val;
            $val = (is_object($val) && !method_exists($val, '__toString')) ? '' : (string) $val;
            $str = preg_replace('/' . $begin . $placeholder . $end .'/', $val, $str);
        }
        if ($escape) {
            $str = preg_replace('/' . preg_quote($escape) . preg_quote($before) . '/', $before, $str);
        }
        return $options['clean'] ? static::clean($str, $options) : $str;
    }

    /**
     * Cleans up a `Text::insert()` formatted string with given `$options` depending
     * on the `'clean'` option. The goal of this function is to replace all whitespace
     * and unneeded mark-up around place-holders that did not get replaced by `Text::insert()`.
     *
     * @param  string $str     The string to clean.
     * @param  array  $options Available options are:
     *                         - `'before'`: characters marking the start of targeted substring.
     *                         - `'after'`: characters marking the end of targeted substring.
     *                         - `'escape'`: The character or string used to escape the before character or string
     *                           (defaults to `'\\'`).
     *                         - `'gap'`: Regular expression matching gaps.
     *                         - `'word'`: Regular expression matching words.
     *                         - `'replacement'`: String to use for cleaned substrings (defaults to `''`).
     * @return string          The cleaned string.
     */
    public static function clean($str, $options = [])
    {
        $options += [
            'before'      => '{:',
            'after'       => '}',
            'escape'      => '\\',
            'word'        => '[\w,.]+',
            'gap'         => '(\s*(?:(?:and|or|,)\s*)?)',
            'replacement' => ''
        ];

        extract($options);

        $begin = $escape ? '(?<!' . preg_quote($escape) . ')' . preg_quote($before) : preg_quote($before);
        $end = preg_quote($options['after']);

        $callback = function ($matches) use ($replacement) {
            if (isset($matches[2]) && isset($matches[3]) && trim($matches[2]) === trim($matches[3])) {
                if (trim($matches[2]) || ($matches[2] && $matches[3])) {
                    return $matches[2] . $replacement;
                }
            }
            return $replacement;
        };
        $str = preg_replace_callback('/(' . $gap. $before . $word . $after . $gap .')+/', $callback, $str);
        if ($escape) {
            $str = preg_replace('/' . preg_quote($escape) . preg_quote($before) . '/', $before, $str);
        }
        return $str;
    }

    /**
     * Generate a string representation of arbitrary data.
     *
     * @param  string $value   The data to dump in string.
     * @param  array  $options Available options are:
     *                         - `'quote'` : dump will quote string data if true (default `true`).
     *                         - `'object'`: dump options for objects.
     *                             - `'method'`: default method to call on string instance (default `__toString`).
     *                         - `'array'` : dump options for arrays.
     *                             - `'indent'`: level of indent (defaults to `1`).
     *                             - `'char'`: indentation character.
     *                             - `'multiplier'`: number of indentation character per indent (default `4`)
     * @return string The dumped string.
     */
    public static function toString($value, $options = [])
    {
        $defaults = [
            'quote'  => '"',
            'object' => [
                'method' => '__toString'
            ],
            'array'  => [
                'indent'     => 1,
                'char'       => ' ',
                'multiplier' => 4
            ]
        ];

        $options += $defaults;

        $options['array'] += $defaults['array'];
        $options['object'] += $defaults['object'];

        if ($value instanceof Closure) {
            return '`Closure`';
        }
        if (is_array($value)) {
            return static::_arrayToString($value, $options);
        }
        if (is_object($value)) {
            return static::_objectToString($value, $options);
        }
        return static::dump($value, $options['quote']);
    }

    /**
     * Generate a string representation of an array.
     *
     * @param  array  $datas   An array.
     * @param  array  $options An array of options.
     * @return string          The dumped string.
     */
    protected static function _arrayToString($datas, $options)
    {
        if (!count($datas)) {
            return '[]';
        }

        extract($options['array']);
        $comma = false;

        $tab = str_repeat($char, $indent * $multiplier);

        $string = "[\n";

        foreach ($datas as $key => $value) {
            if ($comma) {
                $string .= ",\n";
            }
            $comma = true;
            $key = filter_var($key, FILTER_VALIDATE_INT) ? $key : static::dump($key, $options['quote']);
            $string .= $tab . $key . ' => ';
            if (is_array($value)) {
                $options['array']['indent'] = $indent + 1;
                $string .= static::_arrayToString($value, $options);
            } else {
                $string .= static::toString($value, $options);
            }
        }
        $tab = str_repeat($char, ($indent - 1) * $multiplier);
        return $string . "\n" . $tab . "]";
    }

    /**
     * Generate a string representation of an object.
     *
     * @param  array  $value The object.
     * @return string        The dumped string.
     */
    protected static function _objectToString($value, $options)
    {
        if ($value instanceof Exception) {
            $msg = '`' . get_class($value) .'` Code(' . $value->getCode() . ') with ';
            $message = $value->getMessage();
            if ($message) {
                $msg .= 'message '. static::dump($value->getMessage());
            } else {
                $msg .= 'no message';
            }
            return $msg . ' in '. $value->getFile() . ':' . $value->getLine();
        }
        $method = $options['object']['method'];
        if (is_callable($method)) {
            return $method($value);
        }
        if (!$method  || !method_exists($value, $method)) {
            return '`' . get_class($value) . '`';
        }
        return $value->{$method}();
    }

    /**
     * Dump some scalar data using a string representation
     *
     * @param  mixed  $value The scalar data to dump
     * @return string        The dumped string.
     */
    public static function dump($value, $quote = '"')
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_null($value)) {
            return 'null';
        }
        if (!$quote || !is_string($value)) {
            return (string) $value;
        }
        if ($quote === '"') {
            return $quote . static::_dump($value). $quote;
        }
        return $quote . addcslashes($value, $quote) . $quote;
    }

    /**
     * Expands escape sequences and escape special chars in a string.
     *
     * @param  string $string A string which contain escape sequence.
     * @return string         A valid double quotable string.
     */
    protected static function _dump($string)
    {
        $es = ['0', 'x07', 'x08', 't', 'n', 'v', 'f', 'r'];
        $unescaped = '';
        $chars = str_split($string);
        foreach ($chars as $char) {
            if ($char === '') {
                continue;
            }
            $value = ord($char);
            if ($value >= 7 && $value <= 13) {
                $unescaped .= '\\' . $es[$value - 6];
            } elseif ($char === '"' || $char === '$' || $char === '\\') {
                $unescaped .= '\\' . $char;
            } else {
                $unescaped .= $char;
            }
        }
        return $unescaped;
    }
}
