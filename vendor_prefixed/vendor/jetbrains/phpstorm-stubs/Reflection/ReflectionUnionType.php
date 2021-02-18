<?php

namespace WPCOM_VIP;

use WPCOM_VIP\JetBrains\PhpStorm\Pure;
/**
 * @since 8.0
 */
class ReflectionUnionType extends \ReflectionType
{
    /**
     * Get list of named types of union type
     *
     * @return ReflectionNamedType[]
     */
    #[Pure]
    public function getTypes()
    {
    }
}
/**
 * @since 8.0
 */
\class_alias('WPCOM_VIP\\ReflectionUnionType', 'ReflectionUnionType', \false);
