<?php

namespace WPCOM_VIP\Composer\Installers;

class MagentoInstaller extends \WPCOM_VIP\Composer\Installers\BaseInstaller
{
    protected $locations = array('theme' => 'app/design/frontend/{$name}/', 'skin' => 'skin/frontend/default/{$name}/', 'library' => 'lib/{$name}/');
}
