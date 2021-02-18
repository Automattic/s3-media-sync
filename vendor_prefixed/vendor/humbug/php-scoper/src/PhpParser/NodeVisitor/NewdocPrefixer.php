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
use WPCOM_VIP\PhpParser\Node\Scalar\String_;
use WPCOM_VIP\PhpParser\NodeVisitorAbstract;
use function ltrim;
use function strpos;
use function substr;
final class NewdocPrefixer extends \WPCOM_VIP\PhpParser\NodeVisitorAbstract
{
    use StringScoperPrefixer;
    /**
     * @inheritdoc
     */
    public function enterNode(\WPCOM_VIP\PhpParser\Node $node) : \WPCOM_VIP\PhpParser\Node
    {
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Scalar\String_ && $this->isPhpNowdoc($node)) {
            $this->scopeStringValue($node);
        }
        return $node;
    }
    private function isPhpNowdoc(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $node) : bool
    {
        if (\WPCOM_VIP\PhpParser\Node\Scalar\String_::KIND_NOWDOC !== $node->getAttribute('kind')) {
            return \false;
        }
        return 0 === \strpos(\substr(\ltrim($node->value), 0, 5), '<?php');
    }
}
