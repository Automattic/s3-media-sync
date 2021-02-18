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

use WPCOM_VIP\Humbug\PhpScoper\Reflector;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Arg;
use WPCOM_VIP\PhpParser\Node\Const_;
use WPCOM_VIP\PhpParser\Node\Expr\Array_;
use WPCOM_VIP\PhpParser\Node\Expr\ArrayItem;
use WPCOM_VIP\PhpParser\Node\Expr\Assign;
use WPCOM_VIP\PhpParser\Node\Expr\FuncCall;
use WPCOM_VIP\PhpParser\Node\Expr\New_;
use WPCOM_VIP\PhpParser\Node\Expr\StaticCall;
use WPCOM_VIP\PhpParser\Node\Identifier;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Name\FullyQualified;
use WPCOM_VIP\PhpParser\Node\Param;
use WPCOM_VIP\PhpParser\Node\Scalar\String_;
use WPCOM_VIP\PhpParser\Node\Stmt\PropertyProperty;
use WPCOM_VIP\PhpParser\Node\Stmt\Return_;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
use function array_filter;
use function array_key_exists;
use function array_shift;
use function array_values;
use function explode;
use function implode;
use function in_array;
use function is_string;
use function ltrim;
use function preg_match;
use function strpos;
use function strtolower;
/**
 * Prefixes the string scalar values when appropriate.
 *
 * ```
 * $x = 'Foo\Bar';
 * ```
 *
 * =>
 *
 * ```
 * $x = 'Humbug\Foo\Bar';
 * ```
 *
 * @private
 */
final class StringScalarPrefixer extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    private const SPECIAL_FUNCTION_NAMES = ['class_alias', 'class_exists', 'define', 'defined', 'function_exists', 'interface_exists', 'is_a', 'is_subclass_of', 'trait_exists'];
    private const DATETIME_CLASSES = ['datetime', 'datetimeimmutable'];
    private $prefix;
    private $whitelist;
    private $reflector;
    public function __construct(string $prefix, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist, \WPCOM_VIP\Humbug\PhpScoper\Reflector $reflector)
    {
        $this->prefix = $prefix;
        $this->whitelist = $whitelist;
        $this->reflector = $reflector;
    }
    /**
     * @inheritdoc
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        return $node instanceof \WPCOM_VIP\PhpParser\Node\Scalar\String_ ? $this->prefixStringScalar($node) : $node;
    }
    private function prefixStringScalar(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $string) : \WPCOM_VIP\PhpParser\Node\Scalar\String_
    {
        if (\false === (\WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::hasParent($string) && \is_string($string->value)) || 1 !== \preg_match('/^((\\\\)?[\\p{L}_\\d]+)$|((\\\\)?(?:[\\p{L}_\\d]+\\\\+)+[\\p{L}_\\d]+)$/u', $string->value)) {
            return $string;
        }
        $normalizedValue = \ltrim($string->value, '\\');
        if ($this->whitelist->belongsToWhitelistedNamespace($string->value)) {
            return $string;
        }
        // From this point either the symbol belongs to the global namespace or the symbol belongs to the symbol
        // namespace is whitelisted
        $parentNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($string);
        // The string scalar either has a class form or a simple string which can either be a symbol from the global
        // namespace or a completely unrelated string.
        if ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Arg) {
            return $this->prefixStringArg($string, $parentNode, $normalizedValue);
        }
        if ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\ArrayItem) {
            return $this->prefixArrayItemString($string, $parentNode, $normalizedValue);
        }
        if (\false === ($parentNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\Assign || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Param || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Const_ || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\PropertyProperty || $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Return_)) {
            return $string;
        }
        // If belongs to the global namespace then we cannot differentiate the value from a symbol and a regular string
        return $this->belongsToTheGlobalNamespace($string) ? $string : $this->createPrefixedString($string);
    }
    private function prefixStringArg(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $string, \WPCOM_VIP\PhpParser\Node\Arg $parentNode, string $normalizedValue) : \WPCOM_VIP\PhpParser\Node\Scalar\String_
    {
        $callerNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($parentNode);
        if ($callerNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\New_) {
            return $this->prefixNewStringArg($string, $callerNode);
        }
        if ($callerNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall) {
            return $this->prefixFunctionStringArg($string, $callerNode, $normalizedValue);
        }
        if ($callerNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\StaticCall) {
            return $this->prefixStaticCallStringArg($string, $callerNode);
        }
        // If belongs to the global namespace then we cannot differentiate the value from a symbol and a regular
        // string
        return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
    }
    private function prefixNewStringArg(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $string, \WPCOM_VIP\PhpParser\Node\Expr\New_ $newNode) : \WPCOM_VIP\PhpParser\Node\Scalar\String_
    {
        $class = $newNode->class;
        if (\false === $class instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified) {
            return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
        }
        if (\in_array(\strtolower($class->toString()), self::DATETIME_CLASSES, \true)) {
            return $string;
        }
        return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
    }
    private function prefixFunctionStringArg(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $string, \WPCOM_VIP\PhpParser\Node\Expr\FuncCall $functionNode, string $normalizedValue) : \WPCOM_VIP\PhpParser\Node\Scalar\String_
    {
        // In the case of a function call, we allow to prefix strings which could be classes belonging to the global
        // namespace in some cases
        $functionName = $functionNode->name instanceof \WPCOM_VIP\PhpParser\Node\Name ? (string) $functionNode->name : null;
        if (\in_array($functionName, ['date_create', 'date', 'gmdate', 'date_create_from_format'], \true)) {
            return $string;
        }
        if (\false === \in_array($functionName, self::SPECIAL_FUNCTION_NAMES, \true)) {
            return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
        }
        if ('function_exists' === $functionName) {
            return $this->reflector->isFunctionInternal($normalizedValue) ? $string : $this->createPrefixedString($string);
        }
        $isConstantNode = $this->isConstantNode($string);
        if (\false === $isConstantNode) {
            if ('define' === $functionName && $this->belongsToTheGlobalNamespace($string)) {
                return $string;
            }
            return $this->reflector->isClassInternal($normalizedValue) ? $string : $this->createPrefixedString($string);
        }
        return $this->whitelist->isSymbolWhitelisted($string->value, \true) || $this->whitelist->isGlobalWhitelistedConstant($string->value) || $this->reflector->isConstantInternal($normalizedValue) ? $string : $this->createPrefixedString($string);
    }
    private function prefixStaticCallStringArg(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $string, \WPCOM_VIP\PhpParser\Node\Expr\StaticCall $callNode) : \WPCOM_VIP\PhpParser\Node\Scalar\String_
    {
        $class = $callNode->class;
        if (\false === $class instanceof \WPCOM_VIP\PhpParser\Node\Name\FullyQualified) {
            return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
        }
        if (\false === \in_array(\strtolower($class->toString()), self::DATETIME_CLASSES, \true)) {
            return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
        }
        if ($callNode->name instanceof \WPCOM_VIP\PhpParser\Node\Identifier && 'createFromFormat' === $callNode->name->toString()) {
            return $string;
        }
        return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
    }
    private function prefixArrayItemString(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $string, \WPCOM_VIP\PhpParser\Node\Expr\ArrayItem $parentNode, string $normalizedValue) : \WPCOM_VIP\PhpParser\Node\Scalar\String_
    {
        // ArrayItem can lead to two results: either the string is used for `spl_autoload_register()`, e.g.
        // `spl_autoload_register(['Swift', 'autoload'])` in which case the string `'Swift'` is guaranteed to be class
        // name, or something else in which case a string like `'Swift'` can be anything and cannot be prefixed.
        $arrayItemNode = $parentNode;
        $parentNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($parentNode);
        /** @var Array_ $arrayNode */
        $arrayNode = $parentNode;
        $parentNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($parentNode);
        if (\false === $parentNode instanceof \WPCOM_VIP\PhpParser\Node\Arg || null === ($functionNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::findParent($parentNode))) {
            // If belongs to the global namespace then we cannot differentiate the value from a symbol and a regular string
            return $this->belongsToTheGlobalNamespace($string) ? $string : $this->createPrefixedString($string);
        }
        $functionNode = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($parentNode);
        if (\false === $functionNode instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall) {
            // If belongs to the global namespace then we cannot differentiate the value from a symbol and a regular string
            return $this->belongsToTheGlobalNamespace($string) ? $string : $this->createPrefixedString($string);
        }
        /** @var FuncCall $functionNode */
        if (\false === $functionNode->name instanceof \WPCOM_VIP\PhpParser\Node\Name) {
            return $string;
        }
        $functionName = (string) $functionNode->name;
        return 'spl_autoload_register' === $functionName && \array_key_exists(0, $arrayNode->items) && $arrayItemNode === $arrayNode->items[0] && \false === $this->reflector->isClassInternal($normalizedValue) ? $this->createPrefixedString($string) : $string;
    }
    private function isConstantNode(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $node) : bool
    {
        $parent = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($node);
        if (\false === $parent instanceof \WPCOM_VIP\PhpParser\Node\Arg) {
            return \false;
        }
        /** @var Arg $parent */
        $argParent = \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::getParent($parent);
        if (\false === $argParent instanceof \WPCOM_VIP\PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        /* @var FuncCall $argParent */
        if (\false === $argParent->name instanceof \WPCOM_VIP\PhpParser\Node\Name || 'define' !== (string) $argParent->name && 'defined' !== (string) $argParent->name) {
            return \false;
        }
        return $parent === $argParent->args[0];
    }
    private function createPrefixedStringIfDoesNotBelongToGlobalNamespace(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $string) : \WPCOM_VIP\PhpParser\Node\Scalar\String_
    {
        // If belongs to the global namespace then we cannot differentiate the value from a symbol and a regular string
        return $this->belongsToTheGlobalNamespace($string) ? $string : $this->createPrefixedString($string);
    }
    private function createPrefixedString(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $previous) : \WPCOM_VIP\PhpParser\Node\Scalar\String_
    {
        $previousValueParts = \array_values(\array_filter(\explode('\\', $previous->value)));
        if ($this->prefix === $previousValueParts[0]) {
            \array_shift($previousValueParts);
        }
        $previousValue = \implode('\\', $previousValueParts);
        $string = new \WPCOM_VIP\PhpParser\Node\Scalar\String_((string) \WPCOM_VIP\PhpParser\Node\Name\FullyQualified::concat($this->prefix, $previousValue), $previous->getAttributes());
        $string->setAttribute(\WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::PARENT_ATTRIBUTE, $string);
        return $string;
    }
    private function belongsToTheGlobalNamespace(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $string) : bool
    {
        return '' === $string->value || 0 === (int) \strpos($string->value, '\\', 1);
    }
}
