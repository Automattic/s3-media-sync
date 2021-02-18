<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Stmt;

use WPCOM_VIP\PhpParser\Node;
/** Nop/empty statement (;). */
class Nop extends \WPCOM_VIP\PhpParser\Node\Stmt
{
    public function getSubNodeNames() : array
    {
        return [];
    }
    public function getType() : string
    {
        return 'Stmt_Nop';
    }
}
