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
namespace WPCOM_VIP\Humbug\PhpScoper\Scoper\Composer;

use WPCOM_VIP\Humbug\PhpScoper\Scoper;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use function WPCOM_VIP\Humbug\PhpScoper\json_decode;
use function WPCOM_VIP\Humbug\PhpScoper\json_encode;
use function preg_match;
use const JSON_PRETTY_PRINT;
final class InstalledPackagesScoper implements \WPCOM_VIP\Humbug\PhpScoper\Scoper
{
    private static $filePattern = '/composer(\\/|\\\\)installed\\.json$/';
    private $decoratedScoper;
    public function __construct(\WPCOM_VIP\Humbug\PhpScoper\Scoper $decoratedScoper)
    {
        $this->decoratedScoper = $decoratedScoper;
    }
    /**
     * Scopes PHP and JSON files related to Composer.
     *
     * {@inheritdoc}
     */
    public function scope(string $filePath, string $contents, string $prefix, array $patchers, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist) : string
    {
        if (1 !== \preg_match(self::$filePattern, $filePath)) {
            return $this->decoratedScoper->scope($filePath, $contents, $prefix, $patchers, $whitelist);
        }
        $decodedJson = \WPCOM_VIP\Humbug\PhpScoper\json_decode($contents, \false);
        // compatibility with Composer 2
        if (isset($decodedJson->packages)) {
            $decodedJson->packages = $this->prefixLockPackages($decodedJson->packages, $prefix, $whitelist);
        } else {
            $decodedJson = $this->prefixLockPackages((array) $decodedJson, $prefix, $whitelist);
        }
        return \WPCOM_VIP\Humbug\PhpScoper\json_encode($decodedJson, \JSON_PRETTY_PRINT);
    }
    private function prefixLockPackages(array $packages, string $prefix, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist) : array
    {
        foreach ($packages as $index => $package) {
            $packages[$index] = \WPCOM_VIP\Humbug\PhpScoper\Scoper\Composer\AutoloadPrefixer::prefixPackageAutoloadStatements($package, $prefix, $whitelist);
        }
        return $packages;
    }
}
