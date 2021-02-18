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

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\StringScoperPrefixer;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Expr\Eval_;
use WPCOM_VIP\PhpParser\Node\Scalar\String_;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
final class EvalPrefixer extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    use StringScoperPrefixer;
    /**
     * @inheritdoc
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Scalar\String_ && \WPCOM_VIP\Humbug\PhpScoper\PhpParser\NodeVisitor\ParentNodeAppender::findParent($node) instanceof \WPCOM_VIP\PhpParser\Node\Expr\Eval_) {
            $this->scopeStringValue($node);
        }
        return $node;
    }
}
