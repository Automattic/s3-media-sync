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

use WPCOM_VIP\PhpParser\Node\Identifier;
use WPCOM_VIP\PhpParser\Node\Name;
/**
 * Small wrapper to treat an identifier as a name node.
 */
final class NamedIdentifier extends \WPCOM_VIP\PhpParser\Node\Name
{
    private $originalNode;
    public static function create(\WPCOM_VIP\PhpParser\Node\Identifier $node) : self
    {
        $instance = new self($node->name, $node->getAttributes());
        $instance->originalNode = $node;
        return $instance;
    }
    public function getOriginalNode() : \WPCOM_VIP\PhpParser\Node\Identifier
    {
        return $this->originalNode;
    }
}
