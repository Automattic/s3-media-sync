<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Expr;

use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\MatchArm;
class Match_ extends \WPCOM_VIP\PhpParser\Node\Expr
{
    /** @var Node\Expr */
    public $cond;
    /** @var MatchArm[] */
    public $arms;
    /**
     * @param MatchArm[] $arms
     */
    public function __construct(\WPCOM_VIP\PhpParser\Node\Expr $cond, array $arms = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->cond = $cond;
        $this->arms = $arms;
    }
    public function getSubNodeNames() : array
    {
        return ['cond', 'arms'];
    }
    public function getType() : string
    {
        return 'Expr_Match';
    }
}
