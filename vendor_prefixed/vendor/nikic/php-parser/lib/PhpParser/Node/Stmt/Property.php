<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Stmt;

use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Identifier;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\NullableType;
use WPCOM_VIP\PhpParser\Node\UnionType;
class Property extends \WPCOM_VIP\PhpParser\Node\Stmt
{
    /** @var int Modifiers */
    public $flags;
    /** @var PropertyProperty[] Properties */
    public $props;
    /** @var null|Identifier|Name|NullableType|UnionType Type declaration */
    public $type;
    /** @var Node\AttributeGroup[] PHP attribute groups */
    public $attrGroups;
    /**
     * Constructs a class property list node.
     *
     * @param int                                                $flags      Modifiers
     * @param PropertyProperty[]                                 $props      Properties
     * @param array                                              $attributes Additional attributes
     * @param null|string|Identifier|Name|NullableType|UnionType $type       Type declaration
     * @param Node\AttributeGroup[]                              $attrGroups PHP attribute groups
     */
    public function __construct(int $flags, array $props, array $attributes = [], $type = null, array $attrGroups = [])
    {
        $this->attributes = $attributes;
        $this->flags = $flags;
        $this->props = $props;
        $this->type = \is_string($type) ? new \WPCOM_VIP\PhpParser\Node\Identifier($type) : $type;
        $this->attrGroups = $attrGroups;
    }
    public function getSubNodeNames() : array
    {
        return ['attrGroups', 'flags', 'type', 'props'];
    }
    /**
     * Whether the property is explicitly or implicitly public.
     *
     * @return bool
     */
    public function isPublic() : bool
    {
        return ($this->flags & \WPCOM_VIP\PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC) !== 0 || ($this->flags & \WPCOM_VIP\PhpParser\Node\Stmt\Class_::VISIBILITY_MODIFIER_MASK) === 0;
    }
    /**
     * Whether the property is protected.
     *
     * @return bool
     */
    public function isProtected() : bool
    {
        return (bool) ($this->flags & \WPCOM_VIP\PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED);
    }
    /**
     * Whether the property is private.
     *
     * @return bool
     */
    public function isPrivate() : bool
    {
        return (bool) ($this->flags & \WPCOM_VIP\PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
    }
    /**
     * Whether the property is static.
     *
     * @return bool
     */
    public function isStatic() : bool
    {
        return (bool) ($this->flags & \WPCOM_VIP\PhpParser\Node\Stmt\Class_::MODIFIER_STATIC);
    }
    public function getType() : string
    {
        return 'Stmt_Property';
    }
}
