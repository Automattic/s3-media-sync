<?php

namespace WPCOM_VIP\Composer\Installers;

class PuppetInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('module' => 'modules/{$name}/');
}
