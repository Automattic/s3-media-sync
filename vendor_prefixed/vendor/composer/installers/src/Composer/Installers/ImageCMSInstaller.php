<?php

namespace WPCOM_VIP\Composer\Installers;

class ImageCMSInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('template' => 'templates/{$name}/', 'module' => 'application/modules/{$name}/', 'library' => 'application/libraries/{$name}/');
}
