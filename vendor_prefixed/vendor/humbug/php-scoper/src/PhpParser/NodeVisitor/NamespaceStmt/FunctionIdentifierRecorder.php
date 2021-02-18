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

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver;
use WPCOM_VIP\Humbug\PhpScoper\Reflector;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Arg;
use WPCOM_VIP\PhpParser\Node\Expr\FuncCall;
use WPCOM_VIP\PhpParser\Node\Identifier;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Name\FullyQualified;
use WPCOM_VIP\PhpParser\Node\Scalar\String_;
use WPCOM_VIP\PhpParser\Node\Stmt\Function_;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
/**
 * Records the user functions registered in the global namespace which have been whitelisted and whitelisted functions.
 *
 * @private
 */
final class FunctionIdentifierRecorder extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    private $prefix;
    private $nameResolver;
    private $whitelist;
    private $reflector;
    public function __construct(string $prefix, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver $nameResolver, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist, \WPCOM_VIP\Humbug\PhpScoper\Reflector $reflector)
    {
        $this->prefix = $prefix;
        $this->nameResolver = $nameResolver;
        $this->whitelist = $whitelist;
        $this->reflector = $reflector;
    }
    /**
     * @inheritdoc
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        if (\false === ($node instanceof \WPCOM_VIP\PhpParser\Node\Identifier || $node instanceof \WPCOM_VIP\PhpParser\Node\Name || $node instanceof \WPCOM_VIP\PhpParser\Node\Scalar\String_) || \false === \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::hasParent($node)) {
            return $node;
        }
        if (null === ($resolvedName = $this->retrieveResolvedName($node))) {
            return $node;
        }
        if (\false === $this->reflector->isFunctionInternal((string) $resolvedName) && ($this->whitelist->isGlobalWhitelistedFunction((string) $resolvedName) || $this->whitelist->isSymbolWhitelisted((string) $resolvedName))) {
            $this->whitelist->recordWhitelistedFunction($resolvedName, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory::concat($this->prefix, $resolvedName));
        }
        return $node;
    }
    private function retrieveResolvedName(\WPCOM_VIP\PhpParser\Node $node) : ?\WPCOM_VIP\PhpParser\Node\Name\FullyQualified
    {
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Identifier) {
            return $this->retrieveResolvedNameForIdentifier($node);
        }
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Name) {
            return $this->retrieveResolvedNameForFuncCall($node);
        }
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Scalar\String_) {
            return $this->retrieveResolvedNameForString($node);
        }
        return null;
    }
    private function retrieveResolvedNameForIdentifier(\WPCOM_VIP\PhpParser\Node\Identifier $node) : ?\WPCOM_VIP\PhpParser\Node\Name\FullyQualified
    {
        $parent = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($node);
        if (\false === $parent instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Function_ || $node === $parent->returnType) {
            return null;
        }
        $resolvedName = $this->nameResolver->resolveName($node)->getName();
        return $resolvedName instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified ? $resolvedName : null;
    }
    private function retrieveResolvedNameForFuncCall(\WPCOM_VIP\PhpParser\Node\Name $node) : ?\WPCOM_VIP\PhpParser\Node\Name\FullyQualified
    {
        $parent = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($node);
        if (\false === $parent instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        $resolvedName = $this->nameResolver->resolveName($node)->getName();
        return $resolvedName instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified ? $resolvedName : null;
    }
    private function retrieveResolvedNameForString(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $node) : ?\WPCOM_VIP\PhpParser\Node\Name\FullyQualified
    {
        $stringParent = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($node);
        if (\false === $stringParent instanceof \WPCOM_VIP\PhpParser\Node\Arg) {
            return null;
        }
        $argParent = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($stringParent);
        if (\false === $argParent instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall || \false === $argParent->name instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified || 'function_exists' !== (string) $argParent->name) {
            return null;
        }
        $resolvedName = $this->nameResolver->resolveName($node)->getName();
        return $resolvedName instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified ? $resolvedName : null;
    }
}
