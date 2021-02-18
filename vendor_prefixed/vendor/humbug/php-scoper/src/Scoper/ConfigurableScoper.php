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
use function func_get_args;
final class ConfigurableScoper implements \WPCOM_VIP\Humbug\PhpScoper\Scoper
{
    private $decoratedScoper;
    public function __construct(\WPCOM_VIP\Humbug\PhpScoper\Scoper $decoratedScoper)
    {
        $this->decoratedScoper = $decoratedScoper;
    }
    public function withWhitelistedFiles(string ...$whitelistedFiles) : self
    {
        $self = clone $this;
        return [] === $whitelistedFiles ? $self : new self(new \WPCOM_VIP\Humbug\PhpScoper\Scoper\FileWhitelistScoper($self, ...$whitelistedFiles));
    }
    /**
     * @inheritdoc
     */
    public function scope(string $filePath, string $contents, string $prefix, array $patchers, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist) : string
    {
        return $this->decoratedScoper->scope(...\func_get_args());
    }
}
