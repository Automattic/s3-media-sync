<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Builder;

use WPCOM_VIP\PhpParser;
use WPCOM_VIP\PhpParser\BuilderHelpers;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Stmt;
class Namespace_ extends \WPCOM_VIP\PhpParser\Builder\Declaration
{
    private $name;
    private $stmts = [];
    /**
     * Creates a namespace builder.
     *
     * @param Node\Name|string|null $name Name of the namespace
     */
    public function __construct($name)
    {
        $this->name = null !== $name ? \WPCOM_VIP\PhpParser\BuilderHelpers::normalizeName($name) : null;
    }
    /**
     * Adds a statement.
     *
     * @param Node|PhpParser\Builder $stmt The statement to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addStmt($stmt)
    {
        $this->stmts[] = \WPCOM_VIP\PhpParser\BuilderHelpers::normalizeStmt($stmt);
        return $this;
    }
    /**
     * Returns the built node.
     *
     * @return Node The built node
     */
    public function getNode() : \WPCOM_VIP\PhpParser\Node
    {
        return new \WPCOM_VIP\PhpParser\Node\Stmt\Namespace_($this->name, $this->stmts, $this->attributes);
    }
}
