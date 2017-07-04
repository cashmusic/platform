<?php
namespace Kahlan\Jit\Node;

class NodeDef
{
    /**
     * Boolean indicating if a node can be parsed.
     *
     * Use case: when multiple patchers are patching the same code. It's sometimes usefull
     * to mark a node as "unprocessable" and let patchers know that this is not "original code"
     * but already some added patched code.
     *
     * @var boolean
     */
    public $processable = true;

    /**
     * Boolean indicating if code coverage is pertinant on this node.
     *
     * @var boolean
     */
    public $coverable = false;

    /**
     * The node's type.
     *
     * @var string
     */
    public $type = 'none';

    /**
     * The node's namespace.
     *
     * @var instance|null
     */
    public $namespace = null;

    /**
     * The node's parent.
     *
     * @var instance|null
     */
    public $parent = null;

    /**
     * The node's parent function.
     *
     * @var instance|null
     */
    public $function = null;

    /**
     * Boolean indicating if it's a PHP or plain text HTML node.
     *
     * @var boolean
     */
    public $inPhp = false;

    /**
     * Boolean indicating this node is a `trait`, `class` or `interface`.
     *
     * @var boolean
     */
    public $hasMethods = false;

    /**
     * The textual body of the node.
     *
     * @var string
     */
    public $body = '';

    /**
     * The textual closing body of the node (used for function).
     *
     * @var string
     */
    public $close = '';

    /**
     * The children of the node.
     *
     * @var array
     */
    public $tree = [];

    /**
     * Some meta data about the node.
     *
     * @var array
     */
    public $lines = [
        'content' => [],
        'start' => null,
        'stop'  => 0
    ];

    /**
     * The constructor.
     *
     * @param string $body The textual body of the node.
     * @param string $type The type of the node.
     */
    public function __construct($body = '', $type = null)
    {
        if ($type) {
            $this->type = $type;
        }
        $this->body = $body;
    }

    /**
     * Returns the textual representation of the node.
     *
     * @return string
     */
    public function __toString()
    {
        $children = '';
        foreach ($this->tree as $node) {
            $children .= (string) $node;
        }
        return $this->body . $children . $this->close;
    }
}
