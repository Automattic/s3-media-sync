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
use WPCOM_VIP\Humbug\PhpScoper\Scoper\Symfony\XmlScoper as SymfonyXmlScoper;
use WPCOM_VIP\Humbug\PhpScoper\Scoper\Symfony\YamlScoper as SymfonyYamlScoper;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Error as PhpParserError;
use function func_get_args;
/**
 * Scopes the Symfony configuration related files.
 */
final class SymfonyScoper implements \WPCOM_VIP\Humbug\PhpScoper\Scoper
{
    private $decoratedScoper;
    public function __construct(\WPCOM_VIP\Humbug\PhpScoper\Scoper $decoratedScoper)
    {
        $this->decoratedScoper = new \WPCOM_VIP\Humbug\PhpScoper\Scoper\Symfony\XmlScoper(new \WPCOM_VIP\Humbug\PhpScoper\Scoper\Symfony\YamlScoper($decoratedScoper));
    }
    /**
     * Scopes PHP files.
     *
     * {@inheritdoc}
     *
     * @throws PhpParserError
     */
    public function scope(string $filePath, string $contents, string $prefix, array $patchers, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist) : string
    {
        return $this->decoratedScoper->scope(...\func_get_args());
    }
}
