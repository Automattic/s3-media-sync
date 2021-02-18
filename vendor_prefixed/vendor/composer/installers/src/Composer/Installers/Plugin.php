<?php

namespace WPCOM_VIP\Composer\Installers;

use WPCOM_VIP\Composer\Composer;
use WPCOM_VIP\Composer\IO\IOInterface;
use WPCOM_VIP\Composer\Plugin\PluginInterface;
class Plugin implements \WPCOM_VIP\Composer\Plugin\PluginInterface
{
    private $installer;
    public function activate(\WPCOM_VIP\Composer\Composer $composer, \WPCOM_VIP\Composer\IO\IOInterface $io)
    {
        $this->installer = new \WPCOM_VIP\Composer\Installers\Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($this->installer);
    }
    public function deactivate(\WPCOM_VIP\Composer\Composer $composer, \WPCOM_VIP\Composer\IO\IOInterface $io)
    {
        $composer->getInstallationManager()->removeInstaller($this->installer);
    }
    public function uninstall(\WPCOM_VIP\Composer\Composer $composer, \WPCOM_VIP\Composer\IO\IOInterface $io)
    {
    }
}
