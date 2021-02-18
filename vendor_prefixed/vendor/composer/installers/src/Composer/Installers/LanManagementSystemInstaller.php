<?php

namespace WPCOM_VIP\Composer\Installers;

class LanManagementSystemInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('plugin' => 'plugins/{$name}/', 'template' => 'templates/{$name}/', 'document-template' => 'documents/templates/{$name}/', 'userpanel-module' => 'userpanel/modules/{$name}/');
    /**
     * Format package name to CamelCase
     */
    public function inflectPackageVars($vars)
    {
        $vars['name'] = \strtolower(\preg_replace('/(?<=\\w)([A-Z])/', 'WPCOM_VIP\\_\\1', $vars['name']));
        $vars['name'] = \str_replace(array('-', '_'), ' ', $vars['name']);
        $vars['name'] = \str_replace(' ', '', \ucwords($vars['name']));
        return $vars;
    }
}
