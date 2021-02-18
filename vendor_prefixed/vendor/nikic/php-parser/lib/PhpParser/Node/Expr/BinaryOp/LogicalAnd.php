<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Expr\BinaryOp;

use WPCOM_VIP\PhpParser\Node\Expr\BinaryOp;
class LogicalAnd extends \WPCOM_VIP\PhpParser\Node\Expr\BinaryOp
{
    public function getOperatorSigil() : string
    {
        return 'and';
    }
    public function getType() : string
    {
        return 'Expr_BinaryOp_LogicalAnd';
    }
}
