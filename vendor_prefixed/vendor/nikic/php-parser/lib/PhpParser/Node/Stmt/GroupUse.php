<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Node\Stmt;

use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\Stmt;
class GroupUse extends \WPCOM_VIP\PhpParser\Node\Stmt
{
    /** @var int Type of group use */
    public $type;
    /** @var Name Prefix for uses */
    public $prefix;
    /** @var UseUse[] Uses */
    public $uses;
    /**
     * Constructs a group use node.
     *
     * @param Name     $prefix     Prefix for uses
     * @param UseUse[] $uses       Uses
     * @param int      $type       Type of group use
     * @param array    $attributes Additional attributes
     */
    public function __construct(\WPCOM_VIP\PhpParser\Node\Name $prefix, array $uses, int $type = \WPCOM_VIP\PhpParser\Node\Stmt\Use_::TYPE_NORMAL, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->type = $type;
        $this->prefix = $prefix;
        $this->uses = $uses;
    }
    public function getSubNodeNames() : array
    {
        return ['type', 'prefix', 'uses'];
    }
    public function getType() : string
    {
        return 'Stmt_GroupUse';
    }
}
