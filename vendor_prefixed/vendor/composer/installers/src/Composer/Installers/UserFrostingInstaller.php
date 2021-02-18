<?php

namespace WPCOM_VIP\Composer\Installers;

class UserFrostingInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('sprinkle' => 'app/sprinkles/{$name}/');
}
