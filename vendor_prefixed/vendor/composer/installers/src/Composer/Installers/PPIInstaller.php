<?php

namespace WPCOM_VIP\Composer\Installers;

class PPIInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('module' => 'modules/{$name}/');
}
