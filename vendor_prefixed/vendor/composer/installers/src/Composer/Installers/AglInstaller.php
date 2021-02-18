<?php

namespace WPCOM_VIP\Composer\Installers;

class AglInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('module' => 'More/{$name}/');
    /**
     * Format package name to CamelCase
     */
    public function inflectPackageVars($vars)
    {
        $vars['name'] = \preg_replace_callback('/(?:^|_|-)(.?)/', function ($matches) {
            return \strtoupper($matches[1]);
        }, $vars['name']);
        return $vars;
    }
}
