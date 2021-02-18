<?php

namespace WPCOM_VIP\Composer\Installers;

class CodeIgniterInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('library' => 'application/libraries/{$name}/', 'third-party' => 'application/third_party/{$name}/', 'module' => 'application/modules/{$name}/');
}
