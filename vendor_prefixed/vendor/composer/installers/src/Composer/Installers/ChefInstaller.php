<?php

namespace WPCOM_VIP\Composer\Installers;

class ChefInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('cookbook' => 'Chef/{$vendor}/{$name}/', 'role' => 'Chef/roles/{$name}/');
}
