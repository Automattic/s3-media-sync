<?php

namespace WPCOM_VIP\Composer\Installers;

class LavaLiteInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('package' => 'packages/{$vendor}/{$name}/', 'theme' => 'public/themes/{$name}/');
}
