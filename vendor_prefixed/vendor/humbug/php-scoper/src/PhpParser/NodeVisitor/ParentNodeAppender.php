<?php

declare (strict_types=1);
/*
 * This file is part of the humbug/php-scoper package.
 *
 * Copyright (c) 2017 Théo FIDRY <theo.fidry@gmail.com>,
 *                    Pádraic Brady <padraic.brady@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor;

use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
use function array_pop;
use function count;
/**
 * Appends the parent node as an attribute to each node. This allows to have more context in the other visitors when
 * inspecting a node.
 *
 * @private
 */
final class ParentNodeAppender extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    public const PARENT_ATTRIBUTE = 'parent';
    private $stack;
    public static function hasParent(\WPCOM_VIP\PhpParser\Node $node) : bool
    {
        return $node->hasAttribute(self::PARENT_ATTRIBUTE);
    }
    public static function getParent(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        return $node->getAttribute(self::PARENT_ATTRIBUTE);
    }
    public static function findParent(\WPCOM_VIP\PhpParser\Node $node) : ?\WPCOM_VIP\PhpParser\Node
    {
        return $node->hasAttribute(self::PARENT_ATTRIBUTE) ? $node->getAttribute(self::PARENT_ATTRIBUTE) : null;
    }
    /**
     * @inheritdoc
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        $this->stack = [];
        return $nodes;
    }
    /**
     * @inheritdoc
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        if ([] !== $this->stack) {
            $node->setAttribute(self::PARENT_ATTRIBUTE, $this->stack[\count($this->stack) - 1]);
        }
        $this->stack[] = $node;
        return $node;
    }
    /**
     * @inheritdoc
     */
    public function leaveNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        \array_pop($this->stack);
        return $node;
    }
}
