<?php

namespace WPCOM_VIP\Composer\Installers;

class StarbugInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('module' => 'modules/{$name}/', 'theme' => 'themes/{$name}/', 'custom-module' => 'app/modules/{$name}/', 'custom-theme' => 'app/themes/{$name}/');
}
