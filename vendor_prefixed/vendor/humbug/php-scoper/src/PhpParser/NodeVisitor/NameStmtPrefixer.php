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
use WPCOM_VIP\Humbug\PhpScoper\Reflector;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Expr\ArrowFunction;
use WPCOM_VIP\PhpParser\Node\Expr\ClassConstFetch;
use WPCOM_VIP\PhpParser\Node\Expr\ConstFetch;
use WPCOM_VIP\PhpParser\Node\Expr\FuncCall;
use WPCOM_VIP\PhpParser\Node\Expr\Instanceof_;
use WPCOM_VIP\PhpParser\Node\Expr\New_;
use WPCOM_VIP\PhpParser\Node\Expr\StaticCall;
use WPCOM_VIP\PhpParser\Node\Expr\StaticPropertyFetch;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Name\FullyQualified;
use WPCOM_VIP\PhpParser\Node\NullableType;
use WPCOM_VIP\PhpParser\Node\Param;
use WPCOM_VIP\PhpParser\Node\Stmt\Catch_;
use WPCOM_VIP\PhpParser\Node\Stmt\Class_;
use WPCOM_VIP\PhpParser\Node\Stmt\ClassMethod;
use WPCOM_VIP\PhpParser\Node\Stmt\Function_;
use WPCOM_VIP\PhpParser\Node\Stmt\Interface_;
use WPCOM_VIP\PhpParser\Node\Stmt\Property;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
use function in_array;
/**
 * Prefixes names when appropriate.
 *
 * ```
 * new Foo\Bar();
 * ```.
 *
 * =>
 *
 * ```
 * new \Humbug\Foo\Bar();
 * ```
 *
 * @private
 */
final class NameStmtPrefixer extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    public const PHP_FUNCTION_KEYWORDS = ['self', 'static', 'parent'];
    private $prefix;
    private $whitelist;
    private $nameResolver;
    private $reflector;
    public function __construct(string $prefix, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\FullyQualifiedNameResolver $nameResolver, \WPCOM_VIP\Humbug\PhpScoper\Reflector $reflector)
    {
        $this->prefix = $prefix;
        $this->whitelist = $whitelist;
        $this->nameResolver = $nameResolver;
        $this->reflector = $reflector;
    }
    /**
     * @inheritdoc
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        return $node instanceof \WPCOM_VIP\PhpParser\Node\Name && \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::hasParent($node) ? $this->prefixName($node) : $node;
    }
    private function prefixName(\WPCOM_VIP\PhpParser\Node\Name $name) : \WPCOM_VIP\PhpParser\Node
    {
        $parentNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($name);
        if ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\NullableType) {
            if (\false === \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::hasParent($parentNode)) {
                return $name;
            }
            $parentNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($parentNode);
        }
        if (\false === ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\ArrowFunction || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Catch_ || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\ConstFetch || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Class_ || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\ClassConstFetch || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\ClassMethod || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Function_ || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\Instanceof_ || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Interface_ || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\New_ || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Param || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Property || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\StaticCall || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\StaticPropertyFetch)) {
            return $name;
        }
        if (($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Catch_ || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\ClassConstFetch || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\New_ || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\Instanceof_ || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Param || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\StaticCall || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\StaticPropertyFetch) && \in_array((string) $name, self::PHP_FUNCTION_KEYWORDS, \true)) {
            return $name;
        }
        if ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\ConstFetch && 'null' === (string) $name) {
            return $name;
        }
        $resolvedName = $this->nameResolver->resolveName($name)->getName();
        if ($this->prefix === $resolvedName->getFirst() || $this->whitelist->belongsToWhitelistedNamespace((string) $resolvedName)) {
            return $resolvedName;
        }
        // Check if the class can be prefixed
        if (\false === ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\ConstFetch || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall) && $this->reflector->isClassInternal($resolvedName->toString())) {
            return $resolvedName;
        }
        if ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\ConstFetch) {
            if ($this->whitelist->isSymbolWhitelisted($resolvedName->toString(), \true)) {
                return $resolvedName;
            }
            if ($this->reflector->isConstantInternal($resolvedName->toString())) {
                return new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified($resolvedName->toString(), $resolvedName->getAttributes());
            }
            // Constants have an autoloading fallback so we cannot prefix them when the name is ambiguous
            // See https://wiki.php.net/rfc/fallback-to-root-scope-deprecation
            if (\false === $resolvedName instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified) {
                return $resolvedName;
            }
            if ($this->whitelist->isGlobalWhitelistedConstant((string) $resolvedName)) {
                // Unlike classes & functions, whitelisted are not prefixed with aliases registered in scoper-autoload.php
                return new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified($resolvedName->toString(), $resolvedName->getAttributes());
            }
            // Continue
        }
        // Functions have a fallback autoloading so we cannot prefix them when the name is ambiguous
        // See https://wiki.php.net/rfc/fallback-to-root-scope-deprecation
        if ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall) {
            if ($this->reflector->isFunctionInternal($resolvedName->toString())) {
                return new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified($resolvedName->toString(), $resolvedName->getAttributes());
            }
            if (\false === $resolvedName instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified) {
                return $resolvedName;
            }
        }
        if ('self' === (string) $resolvedName && $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\ClassMethod) {
            return $name;
        }
        return \WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory::concat($this->prefix, $resolvedName->toString(), $resolvedName->getAttributes());
    }
}
