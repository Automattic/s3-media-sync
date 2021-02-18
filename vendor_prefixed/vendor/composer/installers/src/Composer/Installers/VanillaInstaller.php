<?php

namespace WPCOM_VIP\Composer\Installers;

class VanillaInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('plugin' => 'plugins/{$name}/', 'theme' => 'themes/{$name}/');
}
