<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Expr\Cast;

use WPCOM_VIP\PhpParser\Node\Expr\Cast;
class String_ extends \WPCOM_VIP\PhpParser\Node\Expr\Cast
{
    public function getType() : string
    {
        return 'Expr_Cast_String';
    }
}
