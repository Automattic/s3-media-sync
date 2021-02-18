<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Builder;

use WPCOM_VIP\PhpParser;
use WPCOM_VIP\PhpParser\BuilderHelpers;
use WPCOM_VIP\PhpParser\Node\Stmt;
class Trait_ extends \WPCOM_VIP\PhpParser\Builder\Declaration
{
    protected $name;
    protected $uses = [];
    protected $properties = [];
    protected $methods = [];
    /**
     * Creates an interface builder.
     *
     * @param string $name Name of the interface
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    /**
     * Adds a statement.
     *
     * @param Stmt|PhpParser\Builder $stmt The statement to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addStmt($stmt)
    {
        $stmt = \WPCOM_VIP\PhpParser\BuilderHelpers::normalizeNode($stmt);
        if ($stmt instanceof \WPCOM_VIP\PhpParser\Node\Stmt\Property) {
            $this->properties[] = $stmt;
        } elseif ($stmt instanceof \WPCOM_VIP\PhpParser\Node\Stmt\ClassMethod) {
            $this->methods[] = $stmt;
        } elseif ($stmt instanceof \WPCOM_VIP\PhpParser\Node\Stmt\TraitUse) {
            $this->uses[] = $stmt;
        } else {
            throw new \LogicException(\sprintf('Unexpected node of type "%s"', $stmt->getType()));
        }
        return $this;
    }
    /**
     * Returns the built trait node.
     *
     * @return Stmt\Trait_ The built interface node
     */
    public function getNode() : \WPCOM_VIP\PhpParser\Node
    {
        return new \WPCOM_VIP\PhpParser\Node\Stmt\Trait_($this->name, ['stmts' => \array_merge($this->uses, $this->properties, $this->methods)], $this->attributes);
    }
}
