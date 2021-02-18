<?php

namespace WPCOM_VIP\Aws\Api\Serializer;

use WPCOM_VIP\Aws\Api\Shape;
use WPCOM_VIP\Aws\Api\ListShape;
/**
 * @internal
 */
class Ec2ParamBuilder extends \WPCOM_VIP\Aws\Api\Serializer\QueryParamBuilder
{
    protected function queryName(\WPCOM_VIP\Aws\Api\Shape $shape, $default = null)
    {
        return ($shape['queryName'] ?: \ucfirst($shape['locationName'])) ?: $default;
    }
    protected function isFlat(\WPCOM_VIP\Aws\Api\Shape $shape)
    {
        return \false;
    }
    protected function format_list(\WPCOM_VIP\Aws\Api\ListShape $shape, array $value, $prefix, &$query)
    {
        // Handle empty list serialization
        if (!$value) {
            $query[$prefix] = \false;
        } else {
            $items = $shape->getMember();
            foreach ($value as $k => $v) {
                $this->format($items, $v, $prefix . '.' . ($k + 1), $query);
            }
        }
    }
}
