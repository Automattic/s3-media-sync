<?php

namespace WPCOM_VIP\Composer\Installers;

class ReIndexInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('theme' => 'themes/{$name}/', 'plugin' => 'plugins/{$name}/');
}
