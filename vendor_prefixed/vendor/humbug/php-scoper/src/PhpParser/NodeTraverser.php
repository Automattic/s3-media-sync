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

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\NameFactory;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Stmt;
use WPCOM_VIP\PhpParser\Node\Stmt\Declare_;
use WPCOM_VIP\PhpParser\Node\Stmt\GroupUse;
use WPCOM_VIP\PhpParser\Node\Stmt\InlineHTML;
use WPCOM_VIP\PhpParser\Node\Stmt\Namespace_;
use WPCOM_VIP\PhpParser\Node\Stmt\Use_;
use WPCOM_VIP\PhpParser\Node\Stmt\UseUse;
use WPCOM_VIP\PhpParser\NodeTraverser as PhpParserNodeTraverser;
use function array_map;
use function array_slice;
use function array_splice;
use function array_values;
use function count;
use function current;
/**
 * @private
 */
final class NodeTraverser extends \WPCOM_VIP\PhpParser\NodeTraverser
{
    /**
     * @inheritdoc
     */
    public function traverse(array $nodes) : array
    {
        $nodes = $this->wrapInNamespace($nodes);
        $nodes = $this->replaceGroupUseStatements($nodes);
        return parent::traverse($nodes);
    }
    /**
     * Wrap the statements in a namespace when necessary:.
     *
     * ```php
     * #!/usr/bin/env php
     * <?php declare(strict_types=1);
     *
     * // A small comment
     *
     * if (\true) {
     *  echo "yo";
     * }
     * ```
     *
     * Will result in:
     *
     * ```php
     * #!/usr/bin/env php
     * <?php declare(strict_types=1);
     *
     * // A small comment
     *
     * namespace {
     *     if (\true) {
     *      echo "yo";
     *     }
     * }
     * ```
     *
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    private function wrapInNamespace(array $nodes) : array
    {
        if ([] === $nodes) {
            return $nodes;
        }
        $nodes = \array_values($nodes);
        $firstRealStatementIndex = 0;
        $realStatements = [];
        foreach ($nodes as $i => $node) {
            if ($node instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Declare_ || $node instanceof \WPCOM_VIP\PhpParser\Node\Stmt\InlineHTML) {
                continue;
            }
            $firstRealStatementIndex = $i;
            /** @var Stmt[] $realStatements */
            $realStatements = \array_slice($nodes, $i);
            break;
        }
        $firstRealStatement = \current($realStatements);
        if (\false !== $firstRealStatement && \false === $firstRealStatement instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Namespace_) {
            $wrappedStatements = new \WPCOM_VIP\PhpParser\Node\Stmt\Namespace_(null, $realStatements);
            \array_splice($nodes, $firstRealStatementIndex, \count($realStatements), [$wrappedStatements]);
        }
        return $nodes;
    }
    /**
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    private function replaceGroupUseStatements(array $nodes) : array
    {
        foreach ($nodes as $node) {
            if (\false === $node instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Namespace_) {
                continue;
            }
            /** @var Namespace_ $node */
            $statements = $node->stmts;
            $newStatements = [];
            foreach ($statements as $statement) {
                if ($statement instanceof \WPCOM_VIP\PhpParser\Node\Stmt\GroupUse) {
                    $uses_ = $this->createUses_($statement);
                    \array_splice($newStatements, \count($newStatements), 0, $uses_);
                } else {
                    $newStatements[] = $statement;
                }
            }
            $node->stmts = $newStatements;
        }
        return $nodes;
    }
    /**
     * @param GroupUse $node
     *
     * @return Use_[]
     */
    private function createUses_(\WPCOM_VIP\PhpParser\Node\Stmt\GroupUse $node) : array
    {
        return \array_map(static function (\WPCOM_VIP\PhpParser\Node\Stmt\UseUse $use) use($node) : Use_ {
            $newUse = new \WPCOM_VIP\PhpParser\Node\Stmt\UseUse(\WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\NameFactory::concat($node->prefix, $use->name, $use->name->getAttributes()), $use->alias, $use->type, $use->getAttributes());
            return new \WPCOM_VIP\PhpParser\Node\Stmt\Use_([$newUse], $node->type, $node->getAttributes());
        }, $node->uses);
    }
}
