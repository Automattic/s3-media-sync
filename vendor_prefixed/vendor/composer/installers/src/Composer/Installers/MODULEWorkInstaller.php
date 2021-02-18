<?php

namespace WPCOM_VIP\Composer\Installers;

class MODULEWorkInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('module' => 'modules/{$name}/');
}
