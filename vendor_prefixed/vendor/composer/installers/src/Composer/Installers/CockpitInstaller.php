<?php

namespace WPCOM_VIP\Composer\Installers;

class CockpitInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('module' => 'cockpit/modules/addons/{$name}/');
    /**
     * Format module name.
     *
     * Strip `module-` prefix from package name.
     *
     * {@inheritDoc}
     */
    public function inflectPackageVars($vars)
    {
        if ($vars['type'] == 'cockpit-module') {
            return $this->inflectModuleVars($vars);
        }
        return $vars;
    }
    public function inflectModuleVars($vars)
    {
        $vars['name'] = \ucfirst(\preg_replace('/cockpit-/i', '', $vars['name']));
        return $vars;
    }
}
