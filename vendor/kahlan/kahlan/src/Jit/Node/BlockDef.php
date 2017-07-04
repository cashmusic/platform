<?php
namespace Kahlan\Jit\Node;

class BlockDef extends NodeDef
{
    /**
     * The node's type.
     *
     * @var string
     */
    public $type = null;

    /**
     * Boolean indicating if this node has methods (i.e class, trait or interface)
     *
     * @var boolean
     */
    public $hasMethods = true;

    /**
     * The name of the node.
     *
     * @var string
     */
    public $name = '';

    /**
     * The defined uses (for class only)
     *
     * @var array
     */
    public $uses = [];

    /**
     * The extended class (for class only)
     *
     * @var string
     */
    public $extends = '';

    /**
     * The implemented interfaces (for class only)
     *
     * @var array|null
     */
    public $implements = null;
}
