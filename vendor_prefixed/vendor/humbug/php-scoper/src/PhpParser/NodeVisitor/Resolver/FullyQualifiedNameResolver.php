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
namespace WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver;

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\NamedIdentifier;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NameStmtPrefixer;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtCollection;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Expr\ConstFetch;
use WPCOM_VIP\PhpParser\Node\Expr\FuncCall;
use WPCOM_VIP\PhpParser\Node\Identifier;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Name\FullyQualified;
use WPCOM_VIP\PhpParser\Node\Scalar\String_;
use function count;
use function in_array;
use function ltrim;
/**
 * Attempts to resolve the node name into a fully qualified node. Returns a valid (non fully-qualified) name node on
 * failure.
 *
 * @private
 */
final class FullyQualifiedNameResolver
{
    private $namespaceStatements;
    private $useStatements;
    public function __construct(\WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection $namespaceStatements, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtCollection $useStatements)
    {
        $this->namespaceStatements = $namespaceStatements;
        $this->useStatements = $useStatements;
    }
    /**
     * @param Name|String_|Identifier $node
     */
    public function resolveName(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\ResolvedValue
    {
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified) {
            return new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\ResolvedValue($node, null, null);
        }
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Scalar\String_) {
            return $this->resolveStringName($node);
        }
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Identifier) {
            $node = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\NamedIdentifier::create($node);
        }
        $namespaceName = $this->namespaceStatements->findNamespaceForNode($node);
        $useName = $this->useStatements->findStatementForNode($namespaceName, $node);
        return new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\ResolvedValue($this->resolveNodeName($node, $namespaceName, $useName), $namespaceName, $useName);
    }
    private function resolveNodeName(\WPCOM_VIP\PhpParser\Node\Name $name, ?\WPCOM_VIP\PhpParser\Node\Name $namespace, ?\WPCOM_VIP\PhpParser\Node\Name $use) : \WPCOM_VIP\PhpParser\Node\Name
    {
        if (null !== $use) {
            return \WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory::concat($use, $name->slice(1), $name->getAttributes());
        }
        if (null === $namespace) {
            return new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified($name, $name->getAttributes());
        }
        if (\in_array((string) $name, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NameStmtPrefixer::PHP_FUNCTION_KEYWORDS, \true)) {
            return $name;
        }
        $parentNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($name);
        if (($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\ConstFetch || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall) && 1 === \count($name->parts)) {
            // Ambiguous name, cannot determine the FQ name
            return $name;
        }
        return \WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory::concat($namespace, $name, $name->getAttributes());
    }
    private function resolveStringName(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $node) : \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\ResolvedValue
    {
        $name = new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified(\ltrim($node->value, '\\'));
        $deducedNamespaceName = $name->slice(0, -1);
        $namespaceName = null;
        if (null !== $deducedNamespaceName) {
            $namespaceName = $this->namespaceStatements->findNamespaceByName($deducedNamespaceName->toString());
        }
        return new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\ResolvedValue($name, $namespaceName, null);
    }
}
