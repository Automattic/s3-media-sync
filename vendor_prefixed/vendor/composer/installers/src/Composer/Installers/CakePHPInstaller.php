<?php

namespace WPCOM_VIP\Composer\Installers;

use WPCOM_VIP\Composer\DependencyResolver\Pool;
use WPCOM_VIP\Composer\Semver\Constraint\Constraint;
class CakePHPInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('plugin' => 'Plugin/{$name}/');
    /**
     * Format package name to CamelCase
     */
    public function inflectPackageVars($vars)
    {
        if ($this->matchesCakeVersion('>=', '3.0.0')) {
            return $vars;
        }
        $nameParts = \explode('/', $vars['name']);
        foreach ($nameParts as &$value) {
            $value = \strtolower(\preg_replace('/(?<=\\w)([A-Z])/', 'WPCOM_VIP\\_\\1', $value));
            $value = \str_replace(array('-', '_'), ' ', $value);
            $value = \str_replace(' ', '', \ucwords($value));
        }
        $vars['name'] = \implode('/', $nameParts);
        return $vars;
    }
    /**
     * Change the default plugin location when cakephp >= 3.0
     */
    public function getLocations()
    {
        if ($this->matchesCakeVersion('>=', '3.0.0')) {
            $this->locations['plugin'] = $this->composer->getConfig()->get('vendor-dir') . '/{$vendor}/{$name}/';
        }
        return $this->locations;
    }
    /**
     * Check if CakePHP version matches against a version
     *
     * @param string $matcher
     * @param string $version
     * @return bool
     */
    protected function matchesCakeVersion($matcher, $version)
    {
        $repositoryManager = $this->composer->getRepositoryManager();
        if (!$repositoryManager) {
            return \false;
        }
        $repos = $repositoryManager->getLocalRepository();
        if (!$repos) {
            return \false;
        }
        return $repos->findPackage('cakephp/cakephp', new \WPCOM_VIP\Composer\Semver\Constraint\Constraint($matcher, $version)) !== null;
    }
}
