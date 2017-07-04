<?php
namespace Kahlan\Jit\Patcher;

use Kahlan\Suite;
use Kahlan\Jit\Node\NodeDef;
use Kahlan\Jit\Node\FunctionDef;

class Monkey
{
    /**
     * Ignoring the following statements which are not valid function or class names.
     *
     * @var array
     */
    protected static $_blacklist = [
        '__halt_compiler' => true,
        'and'             => true,
        'array'           => true,
        'catch'           => true,
        'case'            => true,
        'clone'           => true,
        'compact'         => true,
        'declare'         => true,
        'die'             => true,
        'echo'            => true,
        'elseif'          => true,
        'empty'           => true,
        'eval'            => true,
        'exit'            => true,
        'extract'         => true,
        'for'             => true,
        'foreach'         => true,
        'func_get_arg'    => true,
        'func_get_args'   => true,
        'func_num_args'   => true,
        'function'        => true,
        'if'              => true,
        'include'         => true,
        'include_once'    => true,
        'isset'           => true,
        'list'            => true,
        'or'              => true,
        'parent'          => true,
        'print'           => true,
        'require'         => true,
        'require_once'    => true,
        'return'          => true,
        'self'            => true,
        'static'          => true,
        'switch'          => true,
        'throw'           => true,
        'unset'           => true,
        'while'           => true,
        'xor'             => true
    ];

    /**
     * Prefix to use for custom variable name.
     *
     * @var string
     */
    protected $_prefix = '';

    /**
     * Counter for building unique variable name.
     *
     * @var integer
     */
    protected $_counter = 0;

    /**
     * Uses for the parsed node's namespace.
     *
     * @var array
     */
    protected $_uses = [];

    /**
     * Variables for the parsed node.
     *
     * @var array
     */
    protected $_variables = [];

    /**
     * Nested function depth level.
     *
     * @var integer
     */
    protected $_depth = 0;

    /**
     * The regex.
     *
     * @var string
     */
    protected $_regex = null;

    /**
     * The constructor.
     *
     * @var array $config The config array. Possible values are:
     *                    - `'prefix'` _string_: prefix to use for custom variable name..
     */
    public function __construct($config = [])
    {
        $defaults = [
            'prefix'   => 'KMONKEY'
        ];
        $config += $defaults;

        $this->_prefix   = $config['prefix'];

        $alpha = '[\\\a-zA-Z_\\x7f-\\xff]';
        $alphanum = '[\\\a-zA-Z0-9_\\x7f-\\xff]';
        $this->_regex = "/(new\s+)?(?<!\:|\\\$|\>|{$alphanum})(\s*)({$alpha}{$alphanum}*)(\s*)(?=\(|;|::{$alpha}{$alphanum}*\s*\()/m";
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
        $this->_depth = 0;
        $this->_variables[$this->_depth] = [];
        $this->_processTree($node);
        if ($this->_variables[$this->_depth]) {
            $this->_flushVariables($node);
        }
        $this->_variables = [];
        return $node;
    }

    /**
     * Helper for `Monkey::process()`.
     *
     * @param array $nodes A array of nodes to patch.
     */
    protected function _processTree($parent)
    {
        $hasScope = $parent instanceof FunctionDef || $parent->type === 'namespace';
        if ($hasScope) {
            $this->_variables[++$this->_depth] = [];
        }
        foreach ($parent->tree as $index => $node) {
            if (count($node->tree)) {
                $this->_processTree($node);
            }
            if ($node->processable && $node->type === 'code') {
                $this->_uses = $node->namespace ? $node->namespace->uses : [];

                $this->_monkeyPatch($node, $parent, $index);
            }
        }
        if ($hasScope) {
            $this->_flushVariables($parent);
            $this->_depth--;
        }
    }

    /**
     * Flush stored variables in the passed node.
     *
     * @param array $node The node to store variables in.
     */
    protected function _flushVariables($node)
    {
        if (!$this->_variables[$this->_depth]) {
            return;
        }

        $body = '';
        foreach ($this->_variables[$this->_depth] as $variable) {
            if ($variable['isClass']) {
                $body .= $variable['name'] . '__=null;';
            }
            $body .= $variable['name'] . $variable['patch'];
        }

        if (!$node->inPhp) {
            $body = '<?php ' . $body . ' ?>';
        }

        $patch = new NodeDef($body, 'code');
        $patch->parent = $node;
        $patch->function = $node->function;
        $patch->namespace = $node->namespace;
        array_unshift($node->tree, $patch);
        $this->_variables[$this->_depth] = [];
    }

    /**
     * Monkey patch a node body.
     *
     * @param object  $node   The node to monkey patch.
     * @param array   $parent The parent array.
     * @param integer $index  The index of node in parent children.
     */
    protected function _monkeyPatch($node, $parent, $index)
    {
        if (!preg_match_all($this->_regex, $node->body, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            return;
        }
        $offset = 0;
        foreach (array_reverse($matches) as $match) {
            $len = strlen($match[0][0]);
            $pos = $match[0][1];
            $name = $match[3][0];

            $nextChar = $node->body[$pos + $len];

            $isInstance = !!$match[1][0];
            $isClass = $nextChar === ':' || $isInstance;

            if (!isset(static::$_blacklist[strtolower($name)]) && ($isClass || $nextChar === '(')) {
                $tokens = explode('\\', $name, 2);

                if ($name[0] === '\\') {
                    $name = substr($name, 1);
                    $args = "null , '{$name}'";
                } elseif (isset($this->_uses[$tokens[0]])) {
                    $ns = $this->_uses[$tokens[0]];
                    if (count($tokens) === 2) {
                        $ns .= '\\' . $tokens[1];
                    }
                    $args = "null, '" . $ns . "'";
                } else {
                    $args = "__NAMESPACE__ , '{$name}'";
                }

                if (!isset($this->_variables[$this->_depth][$name])) {
                    $variable = '$__' . $this->_prefix . '__' . $this->_counter++;

                    if ($isClass) {
                        $args .= ', false, ' . $variable . '__';
                    }

                    $this->_variables[$this->_depth][$name] = [
                        'name' => $variable,
                        'isClass' => $isClass,
                        'patch' => "=\Kahlan\Plugin\Monkey::patched({$args});"
                    ];
                } else {
                    $variable = $this->_variables[$this->_depth][$name]['name'];
                }
                $substitute = $variable . '__';
                if (!$isInstance) {
                    $replace = $match[2][0] . $variable . $match[4][0];
                } else {
                    if (Suite::$PHP >= 7 && $this->_addClosingParenthesis($pos + $len, $index, $parent)) {
                        $replace = Suite::$PHP >= 7 ? '(' . $substitute . '?' . $substitute . ':' : '(';
                    } else {
                        $replace = '';
                    }
                    $replace .= $match[1][0] . $match[2][0] . $variable . $match[4][0];
                }
                $node->body = substr_replace($node->body, $replace, $pos, $len);
                $offset = $pos + strlen($replace);
            } else {
                $offset = $pos + $len;
            }
        }
    }

    /**
     * Add a closing parenthesis
     *
     * @param object  $node   The node to monkey patch.
     * @param array   $parent The parent array.
     * @param integer $index  The index of node in parent children.
     * @return boolean        Returns `true` if succeed, `false` otherwise.
     */
    protected function _addClosingParenthesis($pos, $index, $parent)
    {
        $count = 0;
        $nodes = $parent->tree;
        $total = count($nodes);

        for ($i = $index; $i < $total; $i++) {
            $node = $nodes[$i];
            if (!$node->processable || $node->type !== 'code') {
                continue;
            }
            $code = $node->body;
            $len = strlen($code);
            while ($pos < $len) {
                if ($count === 0 && $code[$pos] === ';') {
                    $node->body = substr_replace($code, ');', $pos, 1);
                    return true;
                } elseif ($code[$pos] === '(' || $code[$pos] === '{') {
                    $count++;
                } elseif ($code[$pos] === ')' || $code[$pos] === '}') {
                    $count--;
                    if ($count === 0) {
                        $node->body = substr_replace($code, $code[$pos] . ')', $pos, 1);
                        return true;
                    }
                }
                $pos++;
            }
            $pos = 0;
        }
        return false;
    }

    /**
     * Check if a function is part of the blacklisted ones.
     *
     * @param  string  $name A function name.
     * @return boolean
     */
    public static function blacklisted($name = null)
    {
        if (!func_num_args()) {
            return array_keys(static::$_blacklist);
        }
        return isset(static::$_blacklist[strtolower($name)]);
    }
}
