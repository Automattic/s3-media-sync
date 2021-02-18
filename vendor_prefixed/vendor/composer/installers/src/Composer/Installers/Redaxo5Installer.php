<?php

namespace WPCOM_VIP\Composer\Installers;

class Redaxo5Installer extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('addon' => 'redaxo/src/addons/{$name}/', 'bestyle-plugin' => 'redaxo/src/addons/be_style/plugins/{$name}/');
}
