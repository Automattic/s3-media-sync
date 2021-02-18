<?php

namespace WPCOM_VIP\Composer\Installers;

class ItopInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('extension' => 'extensions/{$name}/');
}
