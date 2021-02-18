<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\NodeVisitor;

use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
/**
 * Visitor cloning all nodes and linking to the original nodes using an attribute.
 *
 * This visitor is required to perform format-preserving pretty prints.
 */
class CloningVisitor extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    public function enterNode(\WPCOM_VIP\PhpParser\Node $origNode)
    {
        $node = clone $origNode;
        $node->setAttribute('origNode', $origNode);
        return $node;
    }
}
