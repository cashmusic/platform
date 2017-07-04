<?php
namespace Kahlan\Spec\Mock;

/**
 * `Collection` class.
 *
 * Example of usage:
 * ```php
 * $traversable = new Traversable(['data' => [0, 1, 2, 3, 4]]);
 *
 * $traversable->first();   // 0
 * $traversable->current(); // 0
 * $traversable->next();    // 1
 * $traversable->next();    // 2
 * $traversable->next();    // 3
 * $traversable->prev();    // 2
 * $traversable->rewind();  // 0
 * ```
 */
class Traversable implements \Iterator
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $_data = [];

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
        $value = next($this->_data);
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

}
