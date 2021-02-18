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

use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Stmt\Namespace_;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
/**
 * @private
 */
final class NamespaceManipulator extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    private const ORIGINAL_NAME_ATTRIBUTE = 'originalName';
    public static function hasOriginalName(\WPCOM_VIP\PhpParser\Node\Stmt\Namespace_ $namespace) : bool
    {
        return $namespace->hasAttribute(self::ORIGINAL_NAME_ATTRIBUTE);
    }
    public static function getOriginalName(\WPCOM_VIP\PhpParser\Node\Stmt\Namespace_ $namespace) : ?\WPCOM_VIP\PhpParser\Node\Name
    {
        if (\false === self::hasOriginalName($namespace)) {
            return $namespace->name;
        }
        return $namespace->getAttribute(self::ORIGINAL_NAME_ATTRIBUTE);
    }
    public static function setOriginalName(\WPCOM_VIP\PhpParser\Node\Stmt\Namespace_ $namespace, ?\WPCOM_VIP\PhpParser\Node\Name $originalName) : void
    {
        $namespace->setAttribute(self::ORIGINAL_NAME_ATTRIBUTE, $originalName);
    }
    private function __construct()
    {
    }
}
