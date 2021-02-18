<?php

namespace WPCOM_VIP\Composer\Installers;

class PhiftyInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('bundle' => 'bundles/{$name}/', 'library' => 'libraries/{$name}/', 'framework' => 'frameworks/{$name}/');
}
