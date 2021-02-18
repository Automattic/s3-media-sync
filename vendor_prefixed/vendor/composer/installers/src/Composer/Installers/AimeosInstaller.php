<?php

namespace WPCOM_VIP\Composer\Installers;

class AimeosInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('extension' => 'ext/{$name}/');
}
