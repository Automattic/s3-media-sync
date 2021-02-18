<?php

namespace WPCOM_VIP\Composer\Installers;

/**
 * An installer to handle MODX specifics when installing packages.
 */
class ModxInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('extra' => 'core/packages/{$name}/');
}
