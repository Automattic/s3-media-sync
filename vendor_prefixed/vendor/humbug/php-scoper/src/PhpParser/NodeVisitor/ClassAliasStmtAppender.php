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

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\ClassAliasFuncCall;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Name\FullyQualified;
use WPCOM_VIP\PhpParser\Node\Stmt;
use WPCOM_VIP\PhpParser\Node\Stmt\Class_;
use WPCOM_VIP\PhpParser\Node\Stmt\Expression;
use WPCOM_VIP\PhpParser\Node\Stmt\Interface_;
use WPCOM_VIP\PhpParser\Node\Stmt\Namespace_;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
use function array_reduce;
/**
 * Appends a `class_alias` to the whitelisted classes.
 *
 * ```
 * namespace A;
 *
 * class Foo
 * {
 * }
 * ```
 *
 * =>
 *
 * ```
 * namespace Humbug\A;
 *
 * class Foo
 * {
 * }
 *
 * class_alias('Humbug\A\Foo', 'A\Foo', false);
 * ```
 *
 * @internal
 */
final class ClassAliasStmtAppender extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    private $prefix;
    private $whitelist;
    private $nameResolver;
    public function __construct(string $prefix, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver $nameResolver)
    {
        $this->prefix = $prefix;
        $this->whitelist = $whitelist;
        $this->nameResolver = $nameResolver;
    }
    /**
     * @inheritdoc
     */
    public function afterTraverse(array $nodes) : array
    {
        $newNodes = [];
        foreach ($nodes as $node) {
            if ($node instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Namespace_) {
                $node = $this->appendToNamespaceStmt($node);
            }
            $newNodes[] = $node;
        }
        return $newNodes;
    }
    private function appendToNamespaceStmt(\WPCOM_VIP\PhpParser\Node\Stmt\Namespace_ $namespace) : \WPCOM_VIP\PhpParser\Node\Stmt\Namespace_
    {
        $namespace->stmts = \array_reduce($namespace->stmts, [$this, 'createNamespaceStmts'], []);
        return $namespace;
    }
    /**
     * @return Stmt[]
     */
    private function createNamespaceStmts(array $stmts, \WPCOM_VIP\PhpParser\Node\Stmt $stmt) : array
    {
        $stmts[] = $stmt;
        if (\false === ($stmt instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Class_ || $stmt instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Interface_)) {
            return $stmts;
        }
        /** @var Class_|Interface_ $stmt */
        if (null === $stmt->name) {
            return $stmts;
        }
        $originalName = $this->nameResolver->resolveName($stmt->name)->getName();
        if (\false === $originalName instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified || $this->whitelist->belongsToWhitelistedNamespace((string) $originalName) || \false === $this->whitelist->isSymbolWhitelisted((string) $originalName) && \false === $this->whitelist->isGlobalWhitelistedClass((string) $originalName)) {
            return $stmts;
        }
        /* @var FullyQualified $originalName */
        $stmts[] = $this->createAliasStmt($originalName, $stmt);
        return $stmts;
    }
    private function createAliasStmt(\WPCOM_VIP\PhpParser\Node\Name\FullyQualified $originalName, \WPCOM_VIP\PhpParser\Node $stmt) : \WPCOM_VIP\PhpParser\Node\Stmt\Expression
    {
        $call = new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\ClassAliasFuncCall(\WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory::concat($this->prefix, $originalName), $originalName, $stmt->getAttributes());
        $expression = new \WPCOM_VIP\PhpParser\Node\Stmt\Expression($call, $stmt->getAttributes());
        $call->setAttribute(\WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::PARENT_ATTRIBUTE, $expression);
        return $expression;
    }
}
