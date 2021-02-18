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
namespace WPCOM_VIP\Humbug\PhpScoper\Scoper;

use WPCOM_VIP\Humbug\PhpScoper\Scoper;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use function array_flip;
use function array_key_exists;
use function func_get_args;
final class FileWhitelistScoper implements \WPCOM_VIP\Humbug\PhpScoper\Scoper
{
    private $decoratedScoper;
    private $filePaths;
    public function __construct(\WPCOM_VIP\Humbug\PhpScoper\Scoper $decoratedScoper, string ...$filePaths)
    {
        $this->decoratedScoper = $decoratedScoper;
        $this->filePaths = \array_flip($filePaths);
    }
    /**
     * @inheritdoc
     */
    public function scope(string $filePath, string $contents, string $prefix, array $patchers, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist) : string
    {
        if (\array_key_exists($filePath, $this->filePaths)) {
            return $contents;
        }
        return $this->decoratedScoper->scope(...\func_get_args());
    }
}
