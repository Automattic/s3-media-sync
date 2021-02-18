<?php

namespace WPCOM_VIP\Composer\Installers;

class EzPlatformInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('meta-assets' => 'web/assets/ezplatform/', 'assets' => 'web/assets/ezplatform/{$name}/');
}
