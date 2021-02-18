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
namespace WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt;

use ArrayIterator;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\NamedIdentifier;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender;
use IteratorAggregate;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Expr\ConstFetch;
use WPCOM_VIP\PhpParser\Node\Expr\FuncCall;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Stmt\ClassLike;
use WPCOM_VIP\PhpParser\Node\Stmt\Function_;
use WPCOM_VIP\PhpParser\Node\Stmt\Use_;
use WPCOM_VIP\PhpParser\Node\Stmt\UseUse;
use function array_key_exists;
use function count;
use function implode;
use function strtolower;
/**
 * Utility class collecting all the use statements for the scoped files allowing to easily find the use which a node
 * may use.
 *
 * @private
 */
final class UseStmtCollection implements \IteratorAggregate
{
    private $hashes = [];
    /**
     * @var Use_[][]
     */
    private $nodes = [null => []];
    public function add(?\WPCOM_VIP\PhpParser\Node\Name $namespaceName, \WPCOM_VIP\PhpParser\Node\Stmt\Use_ $use) : void
    {
        $this->nodes[(string) $namespaceName][] = $use;
    }
    /**
     * Finds the statements matching the given name.
     *
     * $name = 'Foo';
     *
     * use X;
     * use Bar\Foo;
     * use Y;
     *
     * will return the use statement for `Bar\Foo`.
     */
    public function findStatementForNode(?\WPCOM_VIP\PhpParser\Node\Name $namespaceName, \WPCOM_VIP\PhpParser\Node\Name $node) : ?\WPCOM_VIP\PhpParser\Node\Name
    {
        $name = \strtolower($node->getFirst());
        $parentNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::findParent($node);
        if ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\ClassLike && $node instanceof \WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\NamedIdentifier && $node->getOriginalNode() === $parentNode->name) {
            // The current node can either be the class like name or one of its elements, e.g. extends or implements.
            // In the first case, the node was original an Identifier.
            return null;
        }
        $isFunctionName = $this->isFunctionName($node, $parentNode);
        $isConstantName = $this->isConstantName($node, $parentNode);
        $hash = \implode(':', [$namespaceName ? $namespaceName->toString() : '', $name, $isFunctionName ? 'func' : '', $isConstantName ? 'const' : '']);
        if (\array_key_exists($hash, $this->hashes)) {
            return $this->hashes[$hash];
        }
        return $this->hashes[$hash] = $this->find($this->nodes[(string) $namespaceName] ?? [], $isFunctionName, $isConstantName, $name);
    }
    /**
     * @inheritdoc
     */
    public function getIterator() : iterable
    {
        return new \ArrayIterator($this->nodes);
    }
    private function find(array $useStatements, bool $isFunctionName, bool $isConstantName, string $name) : ?\WPCOM_VIP\PhpParser\Node\Name
    {
        foreach ($useStatements as $use_) {
            foreach ($use_->uses as $useStatement) {
                if (\false === $useStatement instanceof \WPCOM_VIP\PhpParser\Node\Stmt\UseUse) {
                    continue;
                }
                $type = \WPCOM_VIP\PhpParser\Node\Stmt\Use_::TYPE_UNKNOWN !== $use_->type ? $use_->type : $useStatement->type;
                if ($name === $useStatement->getAlias()->toLowerString()) {
                    if ($isFunctionName) {
                        if (\WPCOM_VIP\PhpParser\Node\Stmt\Use_::TYPE_FUNCTION === $type) {
                            return \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtManipulator::getOriginalName($useStatement);
                        }
                        continue;
                    }
                    if ($isConstantName) {
                        if (\WPCOM_VIP\PhpParser\Node\Stmt\Use_::TYPE_CONSTANT === $type) {
                            return \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtManipulator::getOriginalName($useStatement);
                        }
                        continue;
                    }
                    if (\WPCOM_VIP\PhpParser\Node\Stmt\Use_::TYPE_NORMAL === $type) {
                        // Match the alias
                        return \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtManipulator::getOriginalName($useStatement);
                    }
                }
            }
        }
        return null;
    }
    private function isFunctionName(\WPCOM_VIP\PhpParser\Node\Name $node, ?\WPCOM_VIP\PhpParser\Node $parentNode) : bool
    {
        if (null === $parentNode || 1 !== \count($node->parts)) {
            return \false;
        }
        if ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall) {
            return \true;
        }
        if (\false === $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Function_) {
            return \false;
        }
        /* @var Function_ $parentNode */
        return $node instanceof \WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\NamedIdentifier && $node->getOriginalNode() === $parentNode->name;
    }
    private function isConstantName(\WPCOM_VIP\PhpParser\Node\Name $node, ?\WPCOM_VIP\PhpParser\Node $parentNode) : bool
    {
        return $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\ConstFetch && 1 === \count($node->parts);
    }
}
