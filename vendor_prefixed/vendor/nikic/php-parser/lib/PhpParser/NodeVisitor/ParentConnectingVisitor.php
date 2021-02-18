<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\NodeVisitor;

use function array_pop;
use function count;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
/**
 * Visitor that connects a child node to its parent node.
 *
 * On the child node, the parent node can be accessed through
 * <code>$node->getAttribute('parent')</code>.
 */
final class ParentConnectingVisitor extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    /**
     * @var Node[]
     */
    private $stack = [];
    public function beforeTraverse(array $nodes)
    {
        $this->stack = [];
    }
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node)
    {
        if (!empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[\count($this->stack) - 1]);
        }
        $this->stack[] = $node;
    }
    public function leaveNode(\WPCOM_VIP\PhpParser\Node $node)
    {
        \array_pop($this->stack);
    }
}
