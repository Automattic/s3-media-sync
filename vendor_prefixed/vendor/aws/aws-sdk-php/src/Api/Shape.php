<?php

namespace WPCOM_VIP\Aws\Api;

/**
 * Base class representing a modeled shape.
 */
class Shape extends \WPCOM_VIP\Aws\Api\AbstractModel
{
    /**
     * Get a concrete shape for the given definition.
     *
     * @param array    $definition
     * @param ShapeMap $shapeMap
     *
     * @return mixed
     * @throws \RuntimeException if the type is invalid
     */
    public static function create(array $definition, \WPCOM_VIP\Aws\Api\ShapeMap $shapeMap)
    {
        static $map = ['structure' => 'WPCOM_VIP\\Aws\\Api\\StructureShape', 'map' => 'WPCOM_VIP\\Aws\\Api\\MapShape', 'list' => 'WPCOM_VIP\\Aws\\Api\\ListShape', 'timestamp' => 'WPCOM_VIP\\Aws\\Api\\TimestampShape', 'integer' => 'WPCOM_VIP\\Aws\\Api\\Shape', 'double' => 'WPCOM_VIP\\Aws\\Api\\Shape', 'float' => 'WPCOM_VIP\\Aws\\Api\\Shape', 'long' => 'WPCOM_VIP\\Aws\\Api\\Shape', 'string' => 'WPCOM_VIP\\Aws\\Api\\Shape', 'byte' => 'WPCOM_VIP\\Aws\\Api\\Shape', 'character' => 'WPCOM_VIP\\Aws\\Api\\Shape', 'blob' => 'WPCOM_VIP\\Aws\\Api\\Shape', 'boolean' => 'WPCOM_VIP\\Aws\\Api\\Shape'];
        if (isset($definition['shape'])) {
            return $shapeMap->resolve($definition);
        }
        if (!isset($map[$definition['type']])) {
            throw new \RuntimeException('Invalid type: ' . \print_r($definition, \true));
        }
        $type = $map[$definition['type']];
        return new $type($definition, $shapeMap);
    }
    /**
     * Get the type of the shape
     *
     * @return string
     */
    public function getType()
    {
        return $this->definition['type'];
    }
    /**
     * Get the name of the shape
     *
     * @return string
     */
    public function getName()
    {
        return $this->definition['name'];
    }
}
