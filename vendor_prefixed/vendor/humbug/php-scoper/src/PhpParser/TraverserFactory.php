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
namespace WPCOM_VIP\Humbug\PhpScoper\PhpParser;

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver;
use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtCollection;
use WPCOM_VIP\Humbug\PhpScoper\Reflector;
use WPCOM_VIP\Humbug\PhpScoper\Scoper\PhpScoper;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\NodeTraverserInterface;
/**
 * @private
 */
class TraverserFactory
{
    private $reflector;
    public function __construct(\WPCOM_VIP\Humbug\PhpScoper\Reflector $reflector)
    {
        $this->reflector = $reflector;
    }
    public function create(\WPCOM_VIP\Humbug\PhpScoper\Scoper\PhpScoper $scoper, string $prefix, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist) : \WPCOM_VIP\PhpParser\NodeTraverserInterface
    {
        $traverser = new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeTraverser();
        $namespaceStatements = new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection();
        $useStatements = new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtCollection();
        $nameResolver = new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver($namespaceStatements, $useStatements);
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender());
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtPrefixer($prefix, $whitelist, $namespaceStatements));
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtCollector($namespaceStatements, $useStatements));
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtPrefixer($prefix, $whitelist, $this->reflector));
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\FunctionIdentifierRecorder($prefix, $nameResolver, $whitelist, $this->reflector));
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ClassIdentifierRecorder($prefix, $nameResolver, $whitelist));
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NameStmtPrefixer($prefix, $whitelist, $nameResolver, $this->reflector));
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\StringScalarPrefixer($prefix, $whitelist, $this->reflector));
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NewdocPrefixer($scoper, $prefix, $whitelist));
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\EvalPrefixer($scoper, $prefix, $whitelist));
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ClassAliasStmtAppender($prefix, $whitelist, $nameResolver));
        $traverser->addVisitor(new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ConstStmtReplacer($whitelist, $nameResolver));
        return $traverser;
    }
}
