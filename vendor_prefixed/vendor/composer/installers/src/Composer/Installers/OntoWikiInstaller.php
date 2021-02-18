<?php

namespace WPCOM_VIP\Composer\Installers;

class OntoWikiInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('extension' => 'extensions/{$name}/', 'theme' => 'extensions/themes/{$name}/', 'translation' => 'extensions/translations/{$name}/');
    /**
     * Format package name to lower case and remove ".ontowiki" suffix
     */
    public function inflectPackageVars($vars)
    {
        $vars['name'] = \strtolower($vars['name']);
        $vars['name'] = \preg_replace('/.ontowiki$/', '', $vars['name']);
        $vars['name'] = \preg_replace('/-theme$/', '', $vars['name']);
        $vars['name'] = \preg_replace('/-translation$/', '', $vars['name']);
        return $vars;
    }
}
