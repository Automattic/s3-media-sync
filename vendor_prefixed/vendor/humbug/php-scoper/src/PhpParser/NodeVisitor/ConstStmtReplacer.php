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

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Arg;
use WPCOM_VIP\PhpParser\Node\Expr\FuncCall;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Name\FullyQualified;
use WPCOM_VIP\PhpParser\Node\Scalar\String_;
use WPCOM_VIP\PhpParser\Node\Stmt\Const_;
use WPCOM_VIP\PhpParser\Node\Stmt\Expression;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
use UnexpectedValueException;
use function count;
/**
 * Replaces const declaration by define when the constant is whitelisted.
 *
 * ```
 * const DUMMY_CONST = 'foo';
 * ```
 *
 * =>
 *
 * ```
 * define('DUMMY_CONST', 'foo');
 * ```
 *
 * It does not do the prefixing.
 *
 * @private
 */
final class ConstStmtReplacer extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    private $whitelist;
    private $nameResolver;
    public function __construct(\WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver $nameResolver)
    {
        $this->whitelist = $whitelist;
        $this->nameResolver = $nameResolver;
    }
    /**
     * {@inheritdoc}
     *
     * @param Const_ $node
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        if (!$node instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Const_) {
            return $node;
        }
        foreach ($node->consts as $constant) {
            /** @var Node\Const_ $constant */
            $resolvedConstantName = $this->nameResolver->resolveName(new \WPCOM_VIP\PhpParser\Node\Name((string) $constant->name, $node->getAttributes()))->getName();
            if (\false === $this->whitelist->isSymbolWhitelisted((string) $resolvedConstantName, \true)) {
                continue;
            }
            if (\count($node->consts) > 1) {
                throw new \UnexpectedValueException('Whitelisting a constant declared in a grouped constant statement (e.g. `const FOO = ' . '\'foo\', BAR = \'bar\'; is not supported. Consider breaking it down in multiple constant ' . 'declaration statements');
            }
            return new \WPCOM_VIP\PhpParser\Node\Stmt\Expression(new \WPCOM_VIP\PhpParser\Node\Expr\FuncCall(new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified('define'), [new \WPCOM_VIP\PhpParser\Node\Arg(new \WPCOM_VIP\PhpParser\Node\Scalar\String_((string) $resolvedConstantName)), new \WPCOM_VIP\PhpParser\Node\Arg($constant->value)]));
        }
        return $node;
    }
}
