<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser;

/**
 * @codeCoverageIgnore
 */
class NodeVisitorAbstract implements \WPCOM_VIP\PhpParser\NodeVisitor
{
    public function beforeTraverse(array $nodes)
    {
        return null;
    }
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node)
    {
        return null;
    }
    public function leaveNode(\WPCOM_VIP\PhpParser\Node $node)
    {
        return null;
    }
    public function afterTraverse(array $nodes)
    {
        return null;
    }
}
