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

use WPCOM_VIP\PhpParser\Node\Name\FullyQualified;
final class PrefixedName extends \WPCOM_VIP\PhpParser\Node\Name\FullyQualified
{
    private $prefixedName;
    private $originalName;
    /**
     * @inheritdoc
     */
    public function __construct(\WPCOM_VIP\PhpParser\Node\Name\FullyQualified $prefixedName, \WPCOM_VIP\PhpParser\Node\Name\FullyQualified $originalName, array $attributes = [])
    {
        parent::__construct($prefixedName, $attributes);
        $this->prefixedName = new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified($prefixedName, $attributes);
        $this->originalName = new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified($originalName, $attributes);
    }
    public function getPrefixedName() : \WPCOM_VIP\PhpParser\Node\Name\FullyQualified
    {
        return $this->prefixedName;
    }
    public function getOriginalName() : \WPCOM_VIP\PhpParser\Node\Name\FullyQualified
    {
        return $this->originalName;
    }
}
