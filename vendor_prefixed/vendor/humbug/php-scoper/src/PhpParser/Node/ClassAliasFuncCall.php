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
namespace WPCOM_VIP\Humbug\PhpScoper\PhpParser\Node;

use WPCOM_VIP\PhpParser\Node\Arg;
use WPCOM_VIP\PhpParser\Node\Expr\ConstFetch;
use WPCOM_VIP\PhpParser\Node\Expr\FuncCall;
use WPCOM_VIP\PhpParser\Node\Name\FullyQualified;
use WPCOM_VIP\PhpParser\Node\Scalar\String_;
final class ClassAliasFuncCall extends \WPCOM_VIP\PhpParser\Node\Expr\FuncCall
{
    /**
     * @inheritdoc
     */
    public function __construct(\WPCOM_VIP\PhpParser\Node\Name\FullyQualified $prefixedName, \WPCOM_VIP\PhpParser\Node\Name\FullyQualified $originalName, array $attributes = [])
    {
        parent::__construct(new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified('class_alias'), [new \WPCOM_VIP\PhpParser\Node\Arg(new \WPCOM_VIP\PhpParser\Node\Scalar\String_((string) $prefixedName)), new \WPCOM_VIP\PhpParser\Node\Arg(new \WPCOM_VIP\PhpParser\Node\Scalar\String_((string) $originalName)), new \WPCOM_VIP\PhpParser\Node\Arg(new \WPCOM_VIP\PhpParser\Node\Expr\ConstFetch(new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified('false')))], $attributes);
    }
}
