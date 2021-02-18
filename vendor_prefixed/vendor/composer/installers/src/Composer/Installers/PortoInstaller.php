<?php

namespace WPCOM_VIP\Composer\Installers;

class PortoInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('container' => 'app/Containers/{$name}/');
}
