<?php
namespace Kahlan\Spec\Mock;

/**
 * `Collection` class.
 *
 * Example of usage:
 * ```php
 * $collection = new Collection();
 * $collection[] = 'foo';
 * // $collection[0] --> 'foo'
 *
 * $collection = new Collection(['data' => ['foo']]);
 * // $collection[0] --> 'foo'
 *
 * $array = iterator_to_array($collection);
 * ```
 *
 * Apart from array-like data access, `Collection`s enable terse and expressive
 * filtering and iteration:
 *
 * ```php
 * $collection = new Collection(['data' => [0, 1, 2, 3, 4]]);
 *
 * $collection->first();   // 0
 * $collection->current(); // 0
 * $collection->next();    // 1
 * $collection->next();    // 2
 * $collection->next();    // 3
 * $collection->prev();    // 2
 * $collection->rewind();  // 0
 * ```
 */
class Collection implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Workaround to allow consistent `unset()` in `foreach`.
     *
     * Note: the edge effet of this behavior is the following:
     * ```php
     * $collection = new Collection(['1', '2', '3']);
     * unset($collection[0]);
     * $collection->next();   // returns 2 instead of 3
     * ```
     */
    protected $_skipNext = false;

    /**
     * The constructor
     *
     * @param array $data The data
     */
    public function __construct($config = [])
    {
        if (isset($config['data'])) {
            $this->_data = $config['data'];
        }
    }

    /**
     * Checks whether or not an offset exists.
     *
     * @param  string  $offset An offset to check for.
     * @return boolean         Returns `true` if offset exists, `false` otherwise.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param  string $offset The offset to retrieve.
     * @return mixed          The value at offset.
     */
    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param  string $offset The offset to assign the value to.
     * @param  mixed  $value  The value to set.
     * @return mixed          The value which was set.
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            return $this->_data[] = $value;
        }
        return $this->_data[$offset] = $value;
    }

    /**
     * Unsets an offset.
     *
     * @param string $offset The offset to unset.
     */
    public function offsetUnset($offset)
    {
        $this->_skipNext = $offset === key($this->_data);
        unset($this->_data[$offset]);
    }

    /**
     * Returns the item keys.
     *
     * @return array The keys of the items.
     */
    public function keys()
    {
        return array_keys($this->_data);
    }

    /**
     * Returns the item values.
     *
     * @return array The keys of the items.
     */
    public function values()
    {
        return array_values($this->_data);
    }

    /**
     * Returns the `$_data` attribute of the collection.
     *
     * @return array
     */
    public function plain()
    {
        return $this->_data;
    }

    /**
     * Returns the key of the current item.
     *
     * @return scalar Scalar on success or `null` on failure.
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * Returns the current item.
     *
     * @return mixed The current item or `false` on failure.
     */
    public function current()
    {
        return current($this->_data);
    }

    /**
     * Moves backward to the previous item.
     *
     * @return mixed The previous item.
     */
    public function prev()
    {
        $value = prev($this->_data);
        return key($this->_data) !== null ? $value : null;
    }

    /**
     * Moves forward to the next item.
     *
     * @return mixed The next item.
     */
    public function next()
    {
        $value = $this->_skipNext ? current($this->_data) : next($this->_data);
        $this->_skipNext = false;
        return key($this->_data) !== null ? $value : null;
    }

    /**
     * Alias to `::rewind()`.
     *
     * @return mixed The first item.
     */
    public function first()
    {
        return $this->rewind();
    }

    /**
     * Rewinds to the first item.
     *
     * @return mixed The current item after rewinding.
     */
    public function rewind()
    {
        return reset($this->_data);
    }

    /**
     * Moves forward to the last item.
     *
     * @return mixed The last item.
     */
    public function end()
    {
        end($this->_data);
        return current($this->_data);
    }

    /**
     * Checks if current position is valid.
     *
     * @return boolean `true` if valid, `false` otherwise.
     */
    public function valid()
    {
        return key($this->_data) !== null;
    }

    /**
     * Counts the items of the object.
     *
     * @return integer Returns the number of items in the collection.
     */
    public function count()
    {
        return count($this->_data);
    }

}
