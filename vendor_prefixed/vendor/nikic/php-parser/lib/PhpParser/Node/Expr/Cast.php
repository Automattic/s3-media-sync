<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Expr;

use WPCOM_VIP\PhpParser\Node\Expr;
abstract class Cast extends \WPCOM_VIP\PhpParser\Node\Expr
{
    /** @var Expr Expression */
    public $expr;
    /**
     * Constructs a cast node.
     *
     * @param Expr  $expr       Expression
     * @param array $attributes Additional attributes
     */
    public function __construct(\WPCOM_VIP\PhpParser\Node\Expr $expr, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->expr = $expr;
    }
    public function getSubNodeNames() : array
    {
        return ['expr'];
    }
}
