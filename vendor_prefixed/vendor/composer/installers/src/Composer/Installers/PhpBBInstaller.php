<?php

namespace WPCOM_VIP\Composer\Installers;

class PhpBBInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('extension' => 'ext/{$vendor}/{$name}/', 'language' => 'language/{$name}/', 'style' => 'styles/{$name}/');
}
