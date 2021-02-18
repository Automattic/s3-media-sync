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

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender;
use WPCOM_VIP\Humbug\PhpScoper\Reflector;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Stmt\Use_;
use WPCOM_VIP\PhpParser\Node\Stmt\UseUse;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
/**
 * Prefixes the use statements.
 *
 * @private
 */
final class UseStmtPrefixer extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    private $prefix;
    private $whitelist;
    private $reflector;
    public function __construct(string $prefix, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist, \WPCOM_VIP\Humbug\PhpScoper\Reflector $reflector)
    {
        $this->prefix = $prefix;
        $this->reflector = $reflector;
        $this->whitelist = $whitelist;
    }
    /**
     * @inheritdoc
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node)
    {
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Stmt\UseUse && $this->shouldPrefixUseStmt($node)) {
            $previousName = $node->name;
            /** @var Name $prefixedName */
            $prefixedName = \WPCOM_VIP\PhpParser\Node\Name::concat($this->prefix, $node->name, $node->name->getAttributes());
            $node->name = $prefixedName;
            \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtManipulator::setOriginalName($node, $previousName);
        }
        return $node;
    }
    private function shouldPrefixUseStmt(\WPCOM_VIP\PhpParser\Node\Stmt\UseUse $use) : bool
    {
        $useType = $this->findUseType($use);
        // If is already from the prefix namespace
        if ($this->prefix === $use->name->getFirst()) {
            return \false;
        }
        // If is whitelisted
        if ($this->whitelist->belongsToWhitelistedNamespace((string) $use->name)) {
            return \false;
        }
        if (\WPCOM_VIP\PhpParser\Node\Stmt\Use_::TYPE_FUNCTION === $useType) {
            return \false === $this->reflector->isFunctionInternal((string) $use->name);
        }
        if (\WPCOM_VIP\PhpParser\Node\Stmt\Use_::TYPE_CONSTANT === $useType) {
            return \false === $this->whitelist->isSymbolWhitelisted((string) $use->name) && \false === $this->reflector->isConstantInternal((string) $use->name);
        }
        return \WPCOM_VIP\PhpParser\Node\Stmt\Use_::TYPE_NORMAL !== $useType || \false === $this->reflector->isClassInternal((string) $use->name);
    }
    /**
     * Finds the type of the use statement.
     *
     * @param UseUse $use
     *
     * @return int See \PhpParser\Node\Stmt\Use_ type constants.
     */
    private function findUseType(\WPCOM_VIP\PhpParser\Node\Stmt\UseUse $use) : int
    {
        if (\WPCOM_VIP\PhpParser\Node\Stmt\Use_::TYPE_UNKNOWN === $use->type) {
            /** @var Use_ $parentNode */
            $parentNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($use);
            return $parentNode->type;
        }
        return $use->type;
    }
}
