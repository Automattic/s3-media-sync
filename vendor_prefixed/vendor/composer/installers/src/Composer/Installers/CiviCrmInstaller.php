<?php

namespace WPCOM_VIP\Composer\Installers;

class CiviCrmInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('ext' => 'ext/{$name}/');
}
