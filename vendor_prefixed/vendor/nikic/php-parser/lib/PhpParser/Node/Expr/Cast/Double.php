<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Expr\Cast;

use WPCOM_VIP\PhpParser\Node\Expr\Cast;
class Double extends \WPCOM_VIP\PhpParser\Node\Expr\Cast
{
    // For use in "kind" attribute
    const KIND_DOUBLE = 1;
    // "double" syntax
    const KIND_FLOAT = 2;
    // "float" syntax
    const KIND_REAL = 3;
    // "real" syntax
    public function getType() : string
    {
        return 'Expr_Cast_Double';
    }
}
