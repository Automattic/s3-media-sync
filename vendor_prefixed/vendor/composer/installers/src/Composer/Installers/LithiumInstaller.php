<?php

namespace WPCOM_VIP\Composer\Installers;

class LithiumInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('library' => 'libraries/{$name}/', 'source' => 'libraries/_source/{$name}/');
}
