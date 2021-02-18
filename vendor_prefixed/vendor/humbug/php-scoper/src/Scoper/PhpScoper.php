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

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\TraverserFactory;
use WPCOM_VIP\Humbug\PhpScoper\Scoper;
use WPCOM_VIP\Humbug\PhpScoper\Whitelist;
use WPCOM_VIP\PhpParser\Error as PhpParserError;
use WPCOM_VIP\PhpParser\Parser;
use WPCOM_VIP\PhpParser\PrettyPrinter\Standard;
use function basename;
use function func_get_args;
use function ltrim;
use function preg_match;
final class PhpScoper implements \WPCOM_VIP\Humbug\PhpScoper\Scoper
{
    private const FILE_PATH_PATTERN = '/\\.php$/';
    private const NOT_FILE_BINARY = '/\\..+?$/';
    private const PHP_TAG = '/^<\\?php/';
    private const PHP_BINARY = '/^#!.+?php.*\\n{1,}<\\?php/';
    private $parser;
    private $decoratedScoper;
    private $traverserFactory;
    public function __construct(\WPCOM_VIP\PhpParser\Parser $parser, \WPCOM_VIP\Humbug\PhpScoper\Scoper $decoratedScoper, \WPCOM_VIP\Humbug\PhpScoper\PhpParser\TraverserFactory $traverserFactory)
    {
        $this->parser = $parser;
        $this->decoratedScoper = $decoratedScoper;
        $this->traverserFactory = $traverserFactory;
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
        if (\false === $this->isPhpFile($filePath, $contents)) {
            return $this->decoratedScoper->scope(...\func_get_args());
        }
        return $this->scopePhp($contents, $prefix, $whitelist);
    }
    public function scopePhp(string $php, string $prefix, \WPCOM_VIP\Humbug\PhpScoper\Whitelist $whitelist) : string
    {
        $statements = $this->parser->parse($php);
        $statements = $this->traverserFactory->create($this, $prefix, $whitelist)->traverse($statements);
        $prettyPrinter = new \WPCOM_VIP\PhpParser\PrettyPrinter\Standard();
        return $prettyPrinter->prettyPrintFile($statements) . "\n";
    }
    private function isPhpFile(string $filePath, string $contents) : bool
    {
        if (1 === \preg_match(self::FILE_PATH_PATTERN, $filePath)) {
            return \true;
        }
        if (1 === \preg_match(self::NOT_FILE_BINARY, \basename($filePath))) {
            return \false;
        }
        if (1 === \preg_match(self::PHP_TAG, \ltrim($contents))) {
            return \true;
        }
        return 1 === \preg_match(self::PHP_BINARY, $contents);
    }
}
