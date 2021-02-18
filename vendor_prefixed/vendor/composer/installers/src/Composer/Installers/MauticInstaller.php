<?php

namespace WPCOM_VIP\Composer\Installers;

class MauticInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('plugin' => 'plugins/{$name}/', 'theme' => 'themes/{$name}/');
    /**
     * Format package name of mautic-plugins to CamelCase
     */
    public function inflectPackageVars($vars)
    {
        if ($vars['type'] == 'mautic-plugin') {
            $vars['name'] = \preg_replace_callback('/(-[a-z])/', function ($matches) {
                return \strtoupper($matches[0][1]);
            }, \ucfirst($vars['name']));
        }
        return $vars;
    }
}
