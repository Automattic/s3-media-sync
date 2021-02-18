<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Scalar\MagicConst;

use WPCOM_VIP\PhpParser\Node\Scalar\MagicConst;
class Namespace_ extends \WPCOM_VIP\PhpParser\Node\Scalar\MagicConst
{
    public function getName() : string
    {
        return '__NAMESPACE__';
    }
    public function getType() : string
    {
        return 'Scalar_MagicConst_Namespace';
    }
}
