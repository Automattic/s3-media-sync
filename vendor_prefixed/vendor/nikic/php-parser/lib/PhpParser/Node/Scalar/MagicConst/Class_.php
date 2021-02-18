<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Scalar\MagicConst;

use WPCOM_VIP\PhpParser\Node\Scalar\MagicConst;
class Class_ extends \WPCOM_VIP\PhpParser\Node\Scalar\MagicConst
{
    public function getName() : string
    {
        return '__CLASS__';
    }
    public function getType() : string
    {
        return 'Scalar_MagicConst_Class';
    }
}
