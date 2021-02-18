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
namespace WPCOM_VIP\Humbug\PhpScoper\Console;

use WPCOM_VIP\Humbug\PhpScoper\Console\Command\AddPrefixCommand;
use WPCOM_VIP\Humbug\PhpScoper\Console\Command\InitCommand;
use WPCOM_VIP\Humbug\PhpScoper\Container;
use WPCOM_VIP\Humbug\PhpScoper\Scoper;
use WPCOM_VIP\Symfony\Component\Filesystem\Filesystem;
/**
 * @final
 * TODO: mark this class as final in the next release
 */
class ApplicationFactory
{
    public function create() : \WPCOM_VIP\Humbug\PhpScoper\Console\Application
    {
        $app = new \WPCOM_VIP\Humbug\PhpScoper\Console\Application(new \WPCOM_VIP\Humbug\PhpScoper\Container(), 'PHP Scoper');
        $app->addCommands([new \WPCOM_VIP\Humbug\PhpScoper\Console\Command\AddPrefixCommand(new \WPCOM_VIP\Symfony\Component\Filesystem\Filesystem(), $app->getContainer()->getScoper()), new \WPCOM_VIP\Humbug\PhpScoper\Console\Command\InitCommand()]);
        return $app;
    }
    /**
     * @deprecated This function will be removed in the next release
     */
    protected static function createScoper() : \WPCOM_VIP\Humbug\PhpScoper\Scoper
    {
        return (new \WPCOM_VIP\Humbug\PhpScoper\Container())->getScoper();
    }
}
