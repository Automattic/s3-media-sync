<?php

declare (strict_types=1);
namespace WPCOM_VIP;

/*
 * This file is part of the humbug/php-scoper package.
 *
 * Copyright (c) 2017 Théo FIDRY <theo.fidry@gmail.com>,
 *                    Pádraic Brady <padraic.brady@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use WPCOM_VIP\Isolated\Symfony\Component\Finder\Finder;
return ['patchers' => [static function (string $filePath, string $prefix, string $contents) : string {
    //
    // PHP-Parser patch
    //
    if ($filePath === \realpath(__DIR__ . '/vendor/nikic/php-parser/lib/PhpParser/NodeAbstract.php')) {
        $length = 15 + \strlen($prefix) + 1;
        return \preg_replace('%strpos\\((.+?)\\) \\+ 15%', \sprintf('strpos($1) + %d', $length), $contents);
    }
    return $contents;
}, static function (string $filePath, string $prefix, string $contents) : string {
    $finderClass = \sprintf('\\%s\\%s', $prefix, \WPCOM_VIP\Isolated\Symfony\Component\Finder\Finder::class);
    return \str_replace($finderClass, '\\' . \WPCOM_VIP\Isolated\Symfony\Component\Finder\Finder::class, $contents);
}]];
