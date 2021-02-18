<?php

namespace WPCOM_VIP\Composer\Installers;

class JoomlaInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('component' => 'components/{$name}/', 'module' => 'modules/{$name}/', 'template' => 'templates/{$name}/', 'plugin' => 'plugins/{$name}/', 'library' => 'libraries/{$name}/');
    // TODO: Add inflector for mod_ and com_ names
}
