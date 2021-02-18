<?php

namespace WPCOM_VIP\Composer\Installers;

class Concrete5Installer extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('core' => 'concrete/', 'block' => 'application/blocks/{$name}/', 'package' => 'packages/{$name}/', 'theme' => 'application/themes/{$name}/', 'update' => 'updates/{$name}/');
}
