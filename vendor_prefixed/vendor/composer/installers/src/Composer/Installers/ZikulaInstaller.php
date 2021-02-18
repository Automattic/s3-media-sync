<?php

namespace WPCOM_VIP\Composer\Installers;

class ZikulaInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('module' => 'modules/{$vendor}-{$name}/', 'theme' => 'themes/{$vendor}-{$name}/');
}
