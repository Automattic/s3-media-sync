<?php

namespace WPCOM_VIP\Composer\Installers;

class RedaxoInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('addon' => 'redaxo/include/addons/{$name}/', 'bestyle-plugin' => 'redaxo/include/addons/be_style/plugins/{$name}/');
}
