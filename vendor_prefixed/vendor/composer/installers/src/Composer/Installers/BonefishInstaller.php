<?php

namespace WPCOM_VIP\Composer\Installers;

class BonefishInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('package' => 'Packages/{$vendor}/{$name}/');
}
