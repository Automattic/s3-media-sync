<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Builder;

use WPCOM_VIP\PhpParser;
use WPCOM_VIP\PhpParser\BuilderHelpers;
use WPCOM_VIP\PhpParser\Node;
use WPCOM_VIP\PhpParser\Node\Stmt;
class Function_ extends \WPCOM_VIP\PhpParser\Builder\FunctionLike
{
    protected $name;
    protected $stmts = [];
    /**
     * Creates a function builder.
     *
     * @param string $name Name of the function
     */
    public function __construct(string $name)
    {
        $this->name = $name;
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
     * Returns the built function node.
     *
     * @return Stmt\Function_ The built function node
     */
    public function getNode() : \WPCOM_VIP\PhpParser\Node
    {
        return new \WPCOM_VIP\PhpParser\Node\Stmt\Function_($this->name, ['byRef' => $this->returnByRef, 'params' => $this->params, 'returnType' => $this->returnType, 'stmts' => $this->stmts], $this->attributes);
    }
}
