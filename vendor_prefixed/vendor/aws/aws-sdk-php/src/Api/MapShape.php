<?php

namespace WPCOM_VIP\Aws\Api;

/**
 * Represents a map shape.
 */
class MapShape extends \WPCOM_VIP\Aws\Api\Shape
{
    /** @var Shape */
    private $value;
    /** @var Shape */
    private $key;
    public function __construct(array $definition, \WPCOM_VIP\Aws\Api\ShapeMap $shapeMap)
    {
        $definition['type'] = 'map';
        parent::__construct($definition, $shapeMap);
    }
    /**
     * @return Shape
     * @throws \RuntimeException if no value is specified
     */
    public function getValue()
    {
        if (!$this->value) {
            if (!isset($this->definition['value'])) {
                throw new \RuntimeException('No value specified');
            }
            $this->value = \WPCOM_VIP\Aws\Api\Shape::create($this->definition['value'], $this->shapeMap);
        }
        return $this->value;
    }
    /**
     * @return Shape
     */
    public function getKey()
    {
        if (!$this->key) {
            $this->key = isset($this->definition['key']) ? \WPCOM_VIP\Aws\Api\Shape::create($this->definition['key'], $this->shapeMap) : new \WPCOM_VIP\Aws\Api\Shape(['type' => 'string'], $this->shapeMap);
        }
        return $this->key;
    }
}