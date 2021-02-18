<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node;

use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\NodeAbstract;
class AttributeGroup extends \WPCOM_VIP\PhpParser\NodeAbstract
{
    /** @var Attribute[] Attributes */
    public $attrs;
    /**
     * @param Attribute[] $attrs PHP attributes
     * @param array $attributes Additional node attributes
     */
    public function __construct(array $attrs, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->attrs = $attrs;
    }
    public function getSubNodeNames() : array
    {
        return ['attrs'];
    }
    public function getType() : string
    {
        return 'AttributeGroup';
    }
}
