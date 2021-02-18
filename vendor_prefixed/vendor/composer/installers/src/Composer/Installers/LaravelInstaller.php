<?php

namespace WPCOM_VIP\Composer\Installers;

class LaravelInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('library' => 'libraries/{$name}/');
}
