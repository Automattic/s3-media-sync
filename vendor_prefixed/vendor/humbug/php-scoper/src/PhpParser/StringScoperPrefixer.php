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

use WPCOM_VIP\Humbug\PhpScoper\Scoper\PhpScoper;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Error as PhpParserError;
use WPCOM_VIP\PhpParser\Node\Scalar\String_;
use function substr;
trait StringScoperPrefixer
{
    private $scoper;
    private $prefix;
    private $whitelist;
    public function __construct(\WPCOM_VIP\Humbug\PhpScoper\Scoper\PhpScoper $scoper, string $prefix, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist)
    {
        $this->scoper = $scoper;
        $this->prefix = $prefix;
        $this->whitelist = $whitelist;
    }
    private function scopeStringValue(\WPCOM_VIP\PhpParser\Node\Scalar\String_ $node) : void
    {
        try {
            $lastChar = \substr($node->value, -1);
            $newValue = $this->scoper->scopePhp($node->value, $this->prefix, $this->whitelist);
            if ("\n" !== $lastChar) {
                $newValue = \substr($newValue, 0, -1);
            }
            $node->value = $newValue;
        } catch (\WPCOM_VIP\PhpParser\Error $error) {
            // Continue without scoping the heredoc which for some reasons contains invalid PHP code
        }
    }
}
