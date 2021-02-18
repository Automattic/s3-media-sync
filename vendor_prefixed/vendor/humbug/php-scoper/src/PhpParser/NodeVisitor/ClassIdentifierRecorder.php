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

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Identifier;
use WPCOM_VIP\PhpParser\Node\Name\FullyQualified;
use WPCOM_VIP\PhpParser\Node\Stmt\ClassLike;
use WPCOM_VIP\PhpParser\Node\Stmt\Trait_;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
/**
 * Records the user classes registered in the global namespace which have been whitelisted and whitelisted classes.
 *
 * @private
 */
final class ClassIdentifierRecorder extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    private $prefix;
    private $nameResolver;
    private $whitelist;
    public function __construct(string $prefix, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver $nameResolver, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist)
    {
        $this->prefix = $prefix;
        $this->nameResolver = $nameResolver;
        $this->whitelist = $whitelist;
    }
    /**
     * @inheritdoc
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        if (\false === $node instanceof \WPCOM_VIP\PhpParser\Node\Identifier || \false === \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::hasParent($node)) {
            return $node;
        }
        $parent = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($node);
        if (\false === $parent instanceof \WPCOM_VIP\PhpParser\Node\Stmt\ClassLike || $parent instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Trait_) {
            return $node;
        }
        /** @var ClassLike $parent */
        if (null === $parent->name) {
            return $node;
        }
        /** @var Identifier $node */
        $resolvedName = $this->nameResolver->resolveName($node)->getName();
        if (\false === $resolvedName instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified) {
            return $node;
        }
        /** @var FullyQualified $resolvedName */
        if ($this->whitelist->isGlobalWhitelistedClass((string) $resolvedName) || $this->whitelist->isSymbolWhitelisted((string) $resolvedName)) {
            $this->whitelist->recordWhitelistedClass($resolvedName, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory::concat($this->prefix, $resolvedName));
        }
        return $node;
    }
}
