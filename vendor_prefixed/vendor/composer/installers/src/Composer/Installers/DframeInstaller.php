<?php

namespace WPCOM_VIP\Composer\Installers;

class DframeInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('module' => 'modules/{$vendor}/{$name}/');
}
