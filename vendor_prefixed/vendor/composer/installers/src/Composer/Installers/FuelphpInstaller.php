<?php

namespace WPCOM_VIP\Composer\Installers;

class FuelphpInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('component' => 'components/{$name}/');
}
