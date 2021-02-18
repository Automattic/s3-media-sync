<?php

namespace WPCOM_VIP\Composer\Installers;

class ElggInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('plugin' => 'mod/{$name}/');
}
