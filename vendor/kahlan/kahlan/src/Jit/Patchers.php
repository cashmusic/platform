<?php
namespace Kahlan\Jit;

use BadMethodCallException;

/**
 * Patcher manager
 */
class Patchers
{

    /**
     * The registered patchers.
     */
    protected $_patchers = [];

    /**
     * Adds a patcher.
     *
     * @param  string         $name    The patcher name.
     * @param  object         $patcher A patcher.
     * @return object|boolean          The added patcher instance or `false` on failure.
     */
    public function add($name, $patcher)
    {
        if (!is_object($patcher)) {
            return false;
        }
        return $this->_patchers[$name] = $patcher;
    }

    /**
     * Gets a patcher.
     *
     * @param  string|object  $patcher A patcher class name or an intance.
     * @return object|boolean          The patcher instance or `false` if not founded.
     */
    public function get($name)
    {
        if (isset($this->_patchers[$name])) {
            return $this->_patchers[$name];
        }
    }

    /**
     * Checks if a patcher exist.
     *
     * @param  string  $name The patcher name.
     * @return boolean
     */
    public function exists($name)
    {
        return isset($this->_patchers[$name]);
    }

    /**
     * Removes a patcher.
     *
     * @param string $name The patcher name.
     */
    public function remove($name)
    {
        unset($this->_patchers[$name]);
    }

    /**
     * Removes all patchers.
     *
     * @param string $name The patcher name.
     */
    public function clear()
    {
        $this->_patchers = [];
    }

    /**
     * Runs file loader patchers.
     *
     * @param string $path The original path of the file.
     * @param string       The patched file path to load.
     */
    public function findFile($loader, $class, $file)
    {
        foreach ($this->_patchers as $patcher) {
            $file = $patcher->findFile($loader, $class, $file);
        }
        return $file;
    }

    /**
     * Checks whether a class need to be patched or not.
     *
     * @param  string  $class The class to check.
     * @return boolean
     */
    public function patchable($class)
    {
        foreach ($this->_patchers as $patcher) {
            if ($patcher->patchable($class)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Runs file patchers.
     *
     * @param  string $code The source code to process.
     * @param  string $path The file path of the source code.
     * @return string       The patched source code.
     */
    public function process($code, $path = null)
    {
        if (!$code) {
            return '';
        }
        $nodes = Parser::parse($code);
        foreach ($this->_patchers as $patcher) {
            $patcher->process($nodes, $path);
        }
        return Parser::unparse($nodes);
    }
}
