<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Scalar\MagicConst;

use WPCOM_VIP\PhpParser\Node\Scalar\MagicConst;
class Dir extends \WPCOM_VIP\PhpParser\Node\Scalar\MagicConst
{
    public function getName() : string
    {
        return '__DIR__';
    }
    public function getType() : string
    {
        return 'Scalar_MagicConst_Dir';
    }
}
