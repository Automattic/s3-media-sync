<?php

declare (strict_types=1);
namespace WPCOM_VIP\Set015;

use WPCOM_VIP\Pimple\Container;
class Greeter
{
    public function greet(\WPCOM_VIP\Pimple\Container $c) : string
    {
        return $c['hello'];
    }
}
