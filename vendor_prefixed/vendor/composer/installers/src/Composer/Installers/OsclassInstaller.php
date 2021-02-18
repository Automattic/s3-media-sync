<?php

namespace WPCOM_VIP\Composer\Installers;

class OsclassInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('plugin' => 'oc-content/plugins/{$name}/', 'theme' => 'oc-content/themes/{$name}/', 'language' => 'oc-content/languages/{$name}/');
}
