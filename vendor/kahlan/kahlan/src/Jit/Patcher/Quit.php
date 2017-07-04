<?php
namespace Kahlan\Jit\Patcher;

class Quit
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
        $this->_processTree($node);
        return $node;
    }

    /**
     * Helper for `Quit::process()`.
     *
     * @param array $parent The node instance tor process.
     */
    protected function _processTree($parent)
    {
        $alphanum = '[\\\a-zA-Z0-9_\\x7f-\\xff]';
        $regex = "/(?<!\:|\\\$|\>|{$alphanum})(\s*)((?:exit|die)\s*)([\(|;])/m";

        foreach ($parent->tree as $node) {
            if ($node->processable && $node->type === 'code') {
                $node->body = preg_replace_callback($regex, function ($matches) {
                    return $matches[1] . '\Kahlan\Plugin\Quit::quit' . ($matches[3] === '(' ? '(' : '();');
                }, $node->body);
            }
            if (count($node->tree)) {
                $this->_processTree($node);
            }
        }
    }
}
