<?php
namespace Kahlan\Jit\Patcher;

class Pointcut
{
    /**
     * Class dependencies.
     *
     * @var array
     */
    protected $_classes = [
        'node' => 'Kahlan\Jit\Node\NodeDef',
    ];

    /**
     * Prefix to use for custom variable name
     *
     * @var string
     */
    protected $_prefix = '';


    /**
     * The constructor.
     *
     * @var array $config The config array. Possible values are:
     *                    - `'prefix'` _string_: prefix to use for custom variable name..
     */
    public function __construct($config = [])
    {
        $defaults = [
            'classes'  => [],
            'prefix'   => 'KPOINTCUT'
        ];
        $config += $defaults;

        $this->_classes += $config['classes'];
        $this->_prefix   = $config['prefix'];
    }

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
     * Helper for `Pointcut::process()`.
     *
     * @param array $parent The node instance tor process.
     */
    protected function _processTree($parent)
    {
        foreach ($parent->tree as $node) {
            if ($node->hasMethods && $node->type !== 'interface') {
                $this->_processMethods($node);
            } elseif (count($node->tree)) {
                $this->_processTree($node);
            }
        }
    }

    /**
     * Helper for `Pointcut::process()`.
     *
     * @param array $parent The node instance tor process.
     */
    protected function _processMethods($parent)
    {
        foreach ($parent->tree as $child) {
            if (!$child->processable) {
                continue;
            }
            $process = (
                $child->type === 'function' &&
                $child->isMethod &&
                !isset($child->visibility['abstract'])
            );
            if ($process) {
                $code = $this->_classes['node'];
                $before = new $code($this->_before($child->isGenerator, $child->isVoid), 'code');
                $before->parent = $child;
                $before->function = $child;
                $before->processable = false;
                $before->namespace = $child->namespace;
                array_unshift($child->tree, $before);
            }
        }
    }

    /**
     * Before closure pattern.
     *
     * @return string.
     */
    protected function _before($isGenerator, $isVoid)
    {
        $prefix = $this->_prefix;
        $statement = $isGenerator ? 'yield' : 'return';
        $return = $isVoid ? '' : "{$statement} \$r; ";
        return "\$__{$prefix}_ARGS__ = func_get_args(); \$__{$prefix}_SELF__ = isset(\$this) ? \$this : get_called_class(); if (\$__{$prefix}__ = \Kahlan\Plugin\Pointcut::before(__METHOD__, \$__{$prefix}_SELF__, \$__{$prefix}_ARGS__)) { \$r = \$__{$prefix}__(\$__{$prefix}_ARGS__, \$__{$prefix}_SELF__); {$return}}";
    }
}
