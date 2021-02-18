<?php

namespace WPCOM_VIP\Composer\Installers;

class ClanCatsFrameworkInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('ship' => 'CCF/orbit/{$name}/', 'theme' => 'CCF/app/themes/{$name}/');
}
