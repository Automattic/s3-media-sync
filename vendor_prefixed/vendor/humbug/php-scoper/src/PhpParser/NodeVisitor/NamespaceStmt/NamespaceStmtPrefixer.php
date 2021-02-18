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

use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Stmt\Namespace_;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
/**
 * Prefixes the relevant namespaces.
 *
 * ```
 * namespace Foo;
 * ```
 *
 * =>
 *
 * ```
 * namespace Humbug\Foo;
 * ```
 *
 * @private
 */
final class NamespaceStmtPrefixer extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    private $prefix;
    private $whitelist;
    private $namespaceStatements;
    public function __construct(string $prefix, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection $namespaceStatements)
    {
        $this->prefix = $prefix;
        $this->whitelist = $whitelist;
        $this->namespaceStatements = $namespaceStatements;
    }
    /**
     * @inheritdoc
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        return $node instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Namespace_ ? $this->prefixNamespaceStmt($node) : $node;
    }
    private function prefixNamespaceStmt(\WPCOM_VIP\PhpParser\Node\Stmt\Namespace_ $namespace) : \WPCOM_VIP\PhpParser\Node
    {
        if ($this->shouldPrefixStmt($namespace)) {
            $originalName = $namespace->name;
            $namespace->name = \WPCOM_VIP\PhpParser\Node\Name::concat($this->prefix, $namespace->name);
            \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceManipulator::setOriginalName($namespace, $originalName);
        }
        $this->namespaceStatements->add($namespace);
        return $namespace;
    }
    private function shouldPrefixStmt(\WPCOM_VIP\PhpParser\Node\Stmt\Namespace_ $namespace) : bool
    {
        if ($this->whitelist->isWhitelistedNamespace((string) $namespace->name)) {
            return \false;
        }
        $nameFirstPart = null === $namespace->name ? '' : $namespace->name->getFirst();
        return $this->prefix !== $nameFirstPart;
    }
}
