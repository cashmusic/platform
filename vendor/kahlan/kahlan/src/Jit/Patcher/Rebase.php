<?php
namespace Kahlan\Jit\Patcher;

class Rebase
{

    /**
     * The JIT find file patcher.
     *
     * @param  object $loader The autloader instance.
     * @param  string $class  The fully-namespaced class name.
     * @param  string $file   The correponding finded file path.
     * @return string         The patched file path.
     */
    public function findFile($loader, $class, $file)
    {
        return $file;
    }

    /**
     * The JIT patchable checker.
     *
     * @param  string  $class The fully-namespaced class name to check.
     * @return boolean
     */
    public function patchable($class)
    {
        return true;
    }

    /**
     * The JIT patcher.
     *
     * @param  object $node The node instance to patch.
     * @param  string $path The file path of the source code.
     * @return object       The patched node.
     */
    public function process($node, $path = null)
    {
        $this->_processTree($node, $path);
        return $node;
    }

    /**
     * Helper for `Rebase::process()`.
     *
     * @param array  $parent The node instance tor process.
     * @param string $path   The file path of the source code.
     */
    protected function _processTree($parent, $path)
    {
        $path = addcslashes($path, "'");
        $dir = "'" . dirname($path) . "'";
        $file = "'" . $path . "'";

        $alphanum  = '[\\\a-zA-Z0-9_\\x7f-\\xff]';
        $dirRegex  = "/(?<!\:|\\\$|\>|{$alphanum})(\s*)(__DIR__)/";
        $fileRegex = "/(?<!\:|\\\$|\>|{$alphanum})(\s*)(__FILE__)/";

        foreach ($parent->tree as $node) {
            if ($node->processable && $node->type === 'code') {
                $node->body = preg_replace($dirRegex, $dir, $node->body);
                $node->body = preg_replace($fileRegex, $file, $node->body);
            }
            if (count($node->tree)) {
                $this->_processTree($node, $path);
            }
        }
    }
}
