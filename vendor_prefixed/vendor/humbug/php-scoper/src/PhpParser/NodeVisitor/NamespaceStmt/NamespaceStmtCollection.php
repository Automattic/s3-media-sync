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
namespace WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt;

use ArrayIterator;
use Countable;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender;
use IteratorAggregate;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Stmt\Namespace_;
use function count;
use function end;
/**
 * Utility class collecting all the namespaces for the scoped files allowing to easily find the namespace to which
 * belongs a node.
 *
 * @private
 */
final class NamespaceStmtCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var Namespace_[]
     */
    private $nodes = [];
    /**
     * @var (Name|null)[] Associative array with the potentially prefixed namespace names as keys and their original name
     *                    as value.
     */
    private $mapping = [];
    /**
     * @param Namespace_ $namespace New namespace, may have been prefixed.
     */
    public function add(\WPCOM_VIP\PhpParser\Node\Stmt\Namespace_ $namespace) : void
    {
        $this->nodes[] = $namespace;
        $this->mapping[(string) $namespace->name] = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceManipulator::getOriginalName($namespace);
    }
    public function findNamespaceForNode(\WPCOM_VIP\PhpParser\Node $node) : ?\WPCOM_VIP\PhpParser\Node\Name
    {
        if (0 === \count($this->nodes)) {
            return null;
        }
        // Shortcut if there is only one namespace
        if (1 === \count($this->nodes)) {
            return \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceManipulator::getOriginalName($this->nodes[0]);
        }
        return $this->getNodeNamespaceName($node);
    }
    public function findNamespaceByName(string $name) : ?\WPCOM_VIP\PhpParser\Node\Name
    {
        foreach ($this->nodes as $node) {
            if ((string) \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceManipulator::getOriginalName($node) === $name) {
                return $node->name;
            }
        }
        return null;
    }
    public function getCurrentNamespaceName() : ?\WPCOM_VIP\PhpParser\Node\Name
    {
        $lastNode = \end($this->nodes);
        return \false === $lastNode ? null : \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceManipulator::getOriginalName($lastNode);
    }
    /**
     * @inheritdoc
     */
    public function count() : int
    {
        return \count($this->nodes);
    }
    private function getNodeNamespaceName(\WPCOM_VIP\PhpParser\Node $node) : ?\WPCOM_VIP\PhpParser\Node\Name
    {
        if (\false === \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::hasParent($node)) {
            return null;
        }
        $parentNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($node);
        if ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Namespace_) {
            return $this->mapping[(string) $parentNode->name];
        }
        return $this->getNodeNamespaceName($parentNode);
    }
    /**
     * @inheritdoc
     */
    public function getIterator() : iterable
    {
        return new \ArrayIterator($this->nodes);
    }
}
