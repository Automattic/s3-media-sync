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

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Stmt\Use_;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
/**
 * Collects all the use statements. This allows us to resolve a class/constant/function call into a fully-qualified
 * call.
 *
 * @private
 */
final class UseStmtCollector extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    private $namespaceStatements;
    private $useStatements;
    public function __construct(\WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection $namespaceStatements, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtCollection $useStatements)
    {
        $this->namespaceStatements = $namespaceStatements;
        $this->useStatements = $useStatements;
    }
    /**
     * @inheritdoc
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Use_) {
            $namespaceName = $this->namespaceStatements->getCurrentNamespaceName();
            $this->useStatements->add($namespaceName, $node);
        }
        return $node;
    }
}
