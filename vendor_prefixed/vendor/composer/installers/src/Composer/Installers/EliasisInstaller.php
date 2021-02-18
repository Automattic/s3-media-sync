<?php

namespace WPCOM_VIP\Composer\Installers;

class EliasisInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('component' => 'components/{$name}/', 'module' => 'modules/{$name}/', 'plugin' => 'plugins/{$name}/', 'template' => 'templates/{$name}/');
}
