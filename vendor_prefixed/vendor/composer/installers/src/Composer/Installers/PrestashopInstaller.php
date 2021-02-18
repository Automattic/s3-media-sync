<?php

namespace WPCOM_VIP\Composer\Installers;

class PrestashopInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('module' => 'modules/{$name}/', 'theme' => 'themes/{$name}/');
}
