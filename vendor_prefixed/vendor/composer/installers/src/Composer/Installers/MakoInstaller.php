<?php

namespace WPCOM_VIP\Composer\Installers;

class MakoInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('package' => 'app/packages/{$name}/');
}
