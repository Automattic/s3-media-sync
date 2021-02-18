<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Expr\AssignOp;

use WPCOM_VIP\PhpParser\Node\Expr\AssignOp;
class Minus extends \WPCOM_VIP\PhpParser\Node\Expr\AssignOp
{
    public function getType() : string
    {
        return 'Expr_AssignOp_Minus';
    }
}
