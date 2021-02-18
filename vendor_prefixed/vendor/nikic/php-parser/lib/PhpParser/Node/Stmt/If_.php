<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Stmt;

use WPCOM_VIP\PhpParser\Node;
class If_ extends \WPCOM_VIP\PhpParser\Node\Stmt
{
    /** @var Node\Expr Condition expression */
    public $cond;
    /** @var Node\Stmt[] Statements */
    public $stmts;
    /** @var ElseIf_[] Elseif clauses */
    public $elseifs;
    /** @var null|Else_ Else clause */
    public $else;
    /**
     * Constructs an if node.
     *
     * @param Node\Expr $cond       Condition
     * @param array     $subNodes   Array of the following optional subnodes:
     *                              'stmts'   => array(): Statements
     *                              'elseifs' => array(): Elseif clauses
     *                              'else'    => null   : Else clause
     * @param array     $attributes Additional attributes
     */
    public function __construct(\WPCOM_VIP\PhpParser\Node\Expr $cond, array $subNodes = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->cond = $cond;
        $this->stmts = $subNodes['stmts'] ?? [];
        $this->elseifs = $subNodes['elseifs'] ?? [];
        $this->else = $subNodes['else'] ?? null;
    }
    public function getSubNodeNames() : array
    {
        return ['cond', 'stmts', 'elseifs', 'else'];
    }
    public function getType() : string
    {
        return 'Stmt_If';
    }
}
