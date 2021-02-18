<?php

namespace WPCOM_VIP\Composer\Installers;

/**
 * An installer to handle TAO extensions.
 */
class TaoInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('extension' => '{$name}');
}
