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
namespace WPCOM_VIP\Humbug\PhpScoper;

use WPCOM_VIP\Humbug\PhpScoper\PhpParser\TraverserFactory;
use WPCOM_VIP\Humbug\PhpScoper\Scoper\Composer\InstalledPackagesScoper;
use WPCOM_VIP\Humbug\PhpScoper\Scoper\Composer\JsonFileScoper;
use WPCOM_VIP\Humbug\PhpScoper\Scoper\NullScoper;
use WPCOM_VIP\Humbug\PhpScoper\Scoper\PatchScoper;
use WPCOM_VIP\Humbug\PhpScoper\Scoper\PhpScoper;
use WPCOM_VIP\Humbug\PhpScoper\Scoper\SymfonyScoper;
use WPCOM_VIP\PhpParser\Lexer;
use WPCOM_VIP\PhpParser\Parser;
use WPCOM_VIP\PhpParser\ParserFactory;
final class Container
{
    private $parser;
    private $reflector;
    private $scoper;
    public function getScoper() : \WPCOM_VIP\Humbug\PhpScoper\Scoper
    {
        if (null === $this->scoper) {
            $this->scoper = new \WPCOM_VIP\Humbug\PhpScoper\Scoper\PatchScoper(new \WPCOM_VIP\Humbug\PhpScoper\Scoper\PhpScoper($this->getParser(), new \WPCOM_VIP\Humbug\PhpScoper\Scoper\Composer\JsonFileScoper(new \WPCOM_VIP\Humbug\PhpScoper\Scoper\Composer\InstalledPackagesScoper(new \WPCOM_VIP\Humbug\PhpScoper\Scoper\SymfonyScoper(new \WPCOM_VIP\Humbug\PhpScoper\Scoper\NullScoper()))), new \WPCOM_VIP\Humbug\PhpScoper\PhpParser\TraverserFactory($this->getReflector())));
        }
        return $this->scoper;
    }
    public function getParser() : \WPCOM_VIP\PhpParser\Parser
    {
        if (null === $this->parser) {
            $this->parser = (new \WPCOM_VIP\PhpParser\ParserFactory())->create(\WPCOM_VIP\PhpParser\ParserFactory::ONLY_PHP7, new \WPCOM_VIP\PhpParser\Lexer());
        }
        return $this->parser;
    }
    public function getReflector() : \WPCOM_VIP\Humbug\PhpScoper\Reflector
    {
        if (null === $this->reflector) {
            $this->reflector = new \WPCOM_VIP\Humbug\PhpScoper\Reflector();
        }
        return $this->reflector;
    }
}
