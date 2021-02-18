<?php

namespace WPCOM_VIP\Composer\Installers;

class SyliusInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('theme' => 'themes/{$name}/');
}
