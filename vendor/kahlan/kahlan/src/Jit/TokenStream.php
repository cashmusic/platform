<?php
namespace Kahlan\Jit;

use Exception;

class TokenStream implements \ArrayAccess, \Countable, \SeekableIterator
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Current pointer key value.
     *
     * @var integer
     */
    protected $_current = 0;

    /**
     * Number of parsed token.
     *
     * @var integer
     */
    private $_count = 0;

    /**
     * Constructor
     *
     * @param string $source Source code
     */
    public function __construct($options = [])
    {
        $defaults = ['source' => '', 'wrap' => false];
        $options += $defaults;
        $this->load($options['source'], $options);
    }

    /**
     * Load the stream using a string (destroy previous loaded tokens)
     *
     * @param string $source Source code
     */
    public function load($source, $options = [])
    {
        $defaults = ['wrap' => false];
        $options += $defaults;

        $wrap = $options['wrap'];

        if ($wrap) {
            $source = "<?php {$source}?>";
        }

        $this->_data = [];
        $this->_current = 0;
        foreach (token_get_all($source) as $token) {
            $this->_data[] = is_array($token) ? $token : [$token, $token, null];
        }
        if ($wrap) {
            $this->_data = array_slice($this->_data, 1, count($this->_data) - 2);
        }
        $this->_count = count($this->_data);
    }

    /**
     * Checks if there is a token of the given type at the given position.
     *
     * @param  integer|string $type  Token type.
     * @param  integer        $index Token position, if none given, consider the current iteration position.
     * @return boolean
     */
    public function is($type, $index = null)
    {
        return $this->getType($index) === $type;
    }

    /**
     * Returns the type of a token.
     *
     * @param  integer $index Token position, if none given, consider the current iteration position.
     * @return mixed
     */
    public function getType($index = null)
    {
        return $this->_getToken($index, 0);
    }

    /**
     * Returns the current token value.
     *
     * @param  integer     $index Token position, if none given, consider the current iteration position.
     * @return string|null
     */
    public function getValue($index = null)
    {
        return $this->_getToken($index, 1);
    }

    /**
     * Returns the current token value.
     *
     * @param  integer     $index Token position, if none given, consider the current iteration position.
     * @return string|null
     */
    protected function _getToken($index, $type)
    {
        if ($index === null) {
            $index = $this->_current;
        }
        return isset($this->_data[$index]) ? $this->_data[$index][$type] : null;
    }

    /**
     * Returns the token type name.
     *
     * @param  integer     $index Token position, if none given, consider the current iteration position.
     * @return string|null
     */
    public function getName($index = null)
    {
        $type = $this->getType($index);
        return is_int($type) ? token_name($type) : null;
    }

    /**
     * Counts the items of the object.
     *
     * @return integer Returns the number of items in the collection.
     */
    public function count()
    {
        return $this->_count;
    }

    /**
     * Checks if there is a token on the current position.
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->_current < $this->_count;
    }

    /**
     * Sets the internal pointer to zero.
     *
     * @var    boolean      If `true` returns the token array. Returns the token value otherwise.
     * @return array|string
     */
    public function rewind($token = false)
    {
        $this->_current = 0;
        return $this->current($token);
    }

    /**
     * Returns the current internal pointer value.
     *
     * @return integer
     */
    public function key()
    {
        return $this->_current;
    }

    /**
     * Returns the current token or the token value.
     *
     * @param  boolean      If `true` returns the token array. Returns the token value otherwise.
     * @return array|string
     */
    public function current($token = false)
    {
        if (!$this->valid()) {
            return null;
        }
        return $token ? $this->_data[$this->_current] : $this->_data[$this->_current][1];
    }

    /**
     * Move to the next token of a given type.
     *
     * @param  mixed       $type Token type to search for.
     * @return string|null Returns the skipped text content (the current is not saved).
     */
    public function next($type = false)
    {
        if ($type === false || $type === true) {
            $this->_current++;
            return $this->current($type);
        }
        $content = '';
        $start = $this->_current++;
        $count = $this->count();

        $list = array_fill_keys((array) $type, true);

        while ($this->_current < $count) {
            $content .= $this->_data[$this->_current][1];
            if (isset($list[$this->_data[$this->_current][0]])) {
                return $content;
            }
            $this->_current++;
        }
        $this->_current = $start;
    }

    /**
     * Moves to the next sequence of tokens.
     *
     * @param  string      $type Tokens sequence to search for.
     * @return array|null  Returns the skipped text content (the current is not saved).
     */
    public function nextSequence($sequence)
    {
        $start = $this->_current;
        $result = '';
        $len = strlen($sequence);
        $lastToken = substr($sequence, -1);

        while (($content = $this->next($lastToken)) !== null) {
            $result .= $content;
            if (strlen($result) >= $len && substr_compare($result, $sequence, -$len, $len) === 0) {
                return $result;
            }
        }
        $this->_current = $start;
    }

    /**
     * Move to the next matching bracket.
     *
     * @return string|null Returns the skipped text content.
     */
    public function nextMatchingBracket()
    {
        if (!$this->valid()) {
            return;
        }

        $matches = ['(' => ')', '{' => '}', '[' => ']'];

        $token = $this->current();
        $content = $open = $token[0];

        if (!isset($matches[$open])) {
            return;
        }
        $level = 1;
        $close = $matches[$open];

        $start = $this->_current;
        $count = $this->count();
        $this->_current++;

        while ($this->_current < $count) {
            $type = $this->_data[$this->_current][0];
            if ($type === $close) {
                $level--;
            } elseif ($type === $open) {
                $level++;
            }
            $content .= $this->_data[$this->_current][1];
            if ($level === 0) {
                return $content;
            }
            $this->_current++;
        }
        $this->_current = $start;
    }

    /**
     * Skips whitespaces and comments next to the current position.
     *
     * @param  boolean $skipComment Skip docblocks as well.
     * @return                      The skipped string.
     */
    public function skipWhitespaces($skipComment = false)
    {
        $skips = [T_WHITESPACE => true];

        if (!$skipComment) {
            $skips += [T_COMMENT => true, T_DOC_COMMENT => true];
        }

        $this->_current++;
        return $this->_skip($skips);
    }

    /**
     * Skips elements until an element doesn't match the elements in the passed array.
     *
     * @param  array $skips The elements array to skip.
     * @return              The skipped string.
     */
    public function skipWhile($skips = [])
    {
        $skips = array_fill_keys($skips, true);
        return $this->_skip($skips);
    }

    /**
     * Skips elements until an element doesn't match the elements in the passed array.
     *
     * @param  array $skips The elements array to skip.
     * @return              The skipped string.
     */
    protected function _skip($skips)
    {
        $skipped = '';
        $count = $this->count();
        while ($this->_current < $count) {
            if (!isset($skips[$this->_data[$this->_current][0]])) {
                break;
            }
            $skipped .= $this->_data[$this->_current][1];
            $this->_current++;
        }
        return $skipped;
    }

    /**
     * Move to previous.
     *
     * @param  boolean      If `true` returns the token array. Returns the token value otherwise.
     * @return array|string
     */
    public function prev($token = false)
    {
        $this->_current--;
        return $this->current($token);
    }

    /**
     * Move to a specific index.
     *
     * @param  integer      $index New position
     * @param  boolean      If `true` returns the token array. Returns the token value otherwise.
     * @return array|string
     */
    public function seek($index, $token = false)
    {
        $this->_current = (int) $index;
        return $this->current($token);
    }

    /**
     * Returns the stream content.
     *
     * @param  mixed  $start Start offset
     * @param  mixed  $end   End offset
     * @return string
     */
    public function source($start = null, $end = null)
    {
        $source = '';
        $start = (int) $start;
        $end = $end === null ? ($this->count() - 1) : (int) $end;
        for ($i = $start; $i <= $end; $i++) {
            $source .= $this->_data[$i][1];
        }
        return $source;
    }

    /**
     * Checks of there is a token with the given index.
     *
     * @param  integer $offset Token index
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    /**
     * Returns a token at the given index.
     *
     * @param  integer $offset Token index
     * @return array
     */
    public function offsetGet($offset)
    {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }

    /**
     * Unsupported
     *
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception('Not supported.');
    }

    /**
     * Unsupported
     *
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception('Not supported.');
    }

    /**
     * Returns the stream source code.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->source();
    }
}
