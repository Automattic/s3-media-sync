<?php

namespace WPCOM_VIP\Composer\Installers;

class ZendInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('library' => 'library/{$name}/', 'extra' => 'extras/library/{$name}/', 'module' => 'module/{$name}/');
}
