<?php

namespace WPCOM_VIP\Composer\Installers;

class WolfCMSInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('plugin' => 'wolf/plugins/{$name}/');
}
