<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Expr;

use WPCOM_VIP\PhpParser\Node\Expr;
class ArrayItem extends \WPCOM_VIP\PhpParser\Node\Expr
{
    /** @var null|Expr Key */
    public $key;
    /** @var Expr Value */
    public $value;
    /** @var bool Whether to assign by reference */
    public $byRef;
    /** @var bool Whether to unpack the argument */
    public $unpack;
    /**
     * Constructs an array item node.
     *
     * @param Expr      $value      Value
     * @param null|Expr $key        Key
     * @param bool      $byRef      Whether to assign by reference
     * @param array     $attributes Additional attributes
     */
    public function __construct(\WPCOM_VIP\PhpParser\Node\Expr $value, \WPCOM_VIP\PhpParser\Node\Expr $key = null, bool $byRef = \false, array $attributes = [], bool $unpack = \false)
    {
        $this->attributes = $attributes;
        $this->key = $key;
        $this->value = $value;
        $this->byRef = $byRef;
        $this->unpack = $unpack;
    }
    public function getSubNodeNames() : array
    {
        return ['key', 'value', 'byRef', 'unpack'];
    }
    public function getType() : string
    {
        return 'Expr_ArrayItem';
    }
}
