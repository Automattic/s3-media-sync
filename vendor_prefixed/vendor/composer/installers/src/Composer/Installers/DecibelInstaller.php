<?php

namespace WPCOM_VIP\Composer\Installers;

class DecibelInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    /** @var array */
    protected $locations = array('app' => 'app/{$name}/');
}
